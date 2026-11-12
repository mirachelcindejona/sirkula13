<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRewardRequest;
use App\Http\Requests\Admin\UpdateRewardRequest;
use App\Models\Reward;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.rewards.index', [
            'rewards' => Reward::query()->withCount('redemptions')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.rewards.create');
    }

    public function store(StoreRewardRequest $request): RedirectResponse
    {
        Reward::query()->create($request->validated());

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil ditambahkan.');
    }

    public function edit(Reward $reward): View
    {
        return view('pages.admin.rewards.edit', compact('reward'));
    }

    public function update(UpdateRewardRequest $request, Reward $reward): RedirectResponse
    {
        $reward->update($request->validated());

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil diperbarui.');
    }
}
