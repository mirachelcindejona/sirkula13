<?php

namespace App\Http\Controllers\Readiness;

use App\Http\Controllers\Controller;
use App\Models\ReadinessCheck;
use App\Models\ReadinessItem;
use App\Services\Residents\ResidentAccess;
use App\Services\Residents\ResidentAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReadinessController extends Controller
{
    public function index(Request $request)
    {
        ResidentAccess::require($request->user(), 'superadmin');
        $items = ReadinessItem::query()->with('check.completedBy')->where('is_active', true)->orderBy('sort_order')->get();

        return view('modules.readiness.index', [
            'items' => $items,
            'completed' => $items->filter(fn ($item) => $item->check?->is_completed)->count(),
        ]);
    }

    public function update(Request $request)
    {
        ResidentAccess::require($request->user(), 'superadmin');
        $data = $request->validate([
            'checks' => ['required', 'array'],
            'checks.*.completed' => ['required', 'boolean'],
            'checks.*.note' => ['nullable', 'string', 'max:2000'],
        ]);
        $items = ReadinessItem::query()->where('is_active', true)->get();
        $provided = array_map('intval', array_keys($data['checks']));
        if (count($provided) !== $items->count() || array_diff($items->pluck('id')->all(), $provided) || array_diff($provided, $items->pluck('id')->all())) {
            throw ValidationException::withMessages(['checks' => 'Daftar checklist tidak lengkap atau memuat item yang tidak sah.']);
        }
        foreach ($items as $item) {
            $itemData = $data['checks'][$item->id];
            if ((bool) $itemData['completed'] && trim((string) ($itemData['note'] ?? '')) === '') {
                throw ValidationException::withMessages(['checks.'.$item->id.'.note' => 'Isi rujukan rapat/SOP sebelum menandai selesai.']);
            }
        }

        DB::transaction(function () use ($items, $data, $request): void {
            foreach ($items as $item) {
                $itemData = $data['checks'][$item->id];
                $completed = (bool) $itemData['completed'];
                $note = trim((string) ($itemData['note'] ?? '')) ?: null;
                $check = ReadinessCheck::query()->where('readiness_item_id', $item->id)->lockForUpdate()->first();
                $before = $check?->only(['is_completed', 'note']) ?? ['is_completed' => false, 'note' => null];
                $check ??= new ReadinessCheck(['readiness_item_id' => $item->id]);
                $check->forceFill([
                    'is_completed' => $completed,
                    'note' => $note,
                    'completed_by' => $completed ? ($check->is_completed ? $check->completed_by : $request->user()->id) : null,
                    'completed_at' => $completed ? ($check->is_completed ? $check->completed_at : now()) : null,
                ])->save();
                $after = $check->only(['is_completed', 'note']);
                if ($before != $after) {
                    ResidentAudit::record($request->user(), 'readiness_check_updated', ReadinessCheck::class, $check->id, $before, $after, $note);
                }
            }
        });

        return redirect()->route('readiness.index')->with('success', 'Checklist kesiapan operasional disimpan.');
    }
}
