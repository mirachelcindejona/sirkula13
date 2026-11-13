<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRewardRequest;
use App\Http\Requests\Admin\UpdateRewardRequest;
use App\Models\Reward;
use App\Support\Listing;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.rewards.index', [
            'rewards' => tap(Reward::query()->withCount('redemptions')->orderBy('name'), function ($query): void {
                Listing::search($query, ['name', 'code']);
                if ($status = Listing::oneOf('status', ['active', 'inactive'])) {
                    $query->where('is_active', $status === 'active');
                }
            })->listing(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.rewards.create');
    }

    public function store(StoreRewardRequest $request): RedirectResponse
    {
        Reward::query()->create($this->payload($request));

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil ditambahkan.');
    }

    public function edit(Reward $reward): View
    {
        return view('pages.admin.rewards.edit', compact('reward'));
    }

    public function update(UpdateRewardRequest $request, Reward $reward): RedirectResponse
    {
        $reward->update($this->payload($request, $reward));

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreRewardRequest|UpdateRewardRequest $request, ?Reward $reward = null): array
    {
        $data = $request->safe()->except('photo');
        if ($request->hasFile('photo')) {
            $data['image_path'] = Uploads::store($request->file('photo'), 'rewards');
            $data['icon'] = null;
        }

        return $data;
    }
}
