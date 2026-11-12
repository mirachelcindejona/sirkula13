<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.program.dashboard', [
            'publishedEvents' => Event::query()->where('status', 'published')->count(),
            'pendingAttendance' => (int) DB::table('event_attendances')->where('status', 'pending_verification')->count(),
            'drafts' => Event::query()->where('status', 'draft')->count() + Product::query()->whereIn('status', ['draft', 'review'])->count(),
        ]);
    }

    public function events(): View
    {
        return view('pages.program.events', [
            'events' => Event::query()->orderByDesc('event_date')->paginate(8),
        ]);
    }

    public function education(): View
    {
        return view('pages.program.education', [
            'materials' => DB::table('education_materials')->orderBy('title')->get(),
        ]);
    }

    public function catalog(): View
    {
        return view('pages.program.catalog', [
            'products' => Product::query()->with('seller')->orderBy('name')->paginate(9),
        ]);
    }

    public function orders(): View
    {
        return view('pages.program.orders', [
            'orders' => ProductOrder::query()->with(['product', 'buyer', 'seller'])->latest()->paginate(10),
        ]);
    }
}
