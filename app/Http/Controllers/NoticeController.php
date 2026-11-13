<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $notices = Notice::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->listing();

        return view('pages.notices.index', compact('notices'));
    }

    public function poll(Request $request): JsonResponse
    {
        $items = Notice::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(8)
            ->get();

        return response()->json([
            'unread' => Notice::query()->where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            'items' => $items->map(fn (Notice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'body' => $notice->body,
                'url' => route('notices.read', $notice),
                'read' => $notice->read_at !== null,
                'time' => $notice->created_at?->locale('id')->diffForHumans(),
            ]),
        ]);
    }

    public function read(Request $request, Notice $notice): RedirectResponse
    {
        abort_unless($notice->user_id === $request->user()->id, 403);
        if ($notice->read_at === null) {
            $notice->forceFill(['read_at' => now()])->save();
        }

        return redirect()->to($this->safeUrl($notice->url));
    }

    public function readAll(Request $request): RedirectResponse
    {
        Notice::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }

    private function safeUrl(?string $url): string
    {
        $app = rtrim(url('/'), '/');
        if ($url && ($url === $app || str_starts_with($url, $app.'/'))) {
            return $url;
        }

        return route('notices.index');
    }
}
