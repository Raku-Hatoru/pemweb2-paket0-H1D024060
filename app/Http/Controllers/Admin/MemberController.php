<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.members.index', [
            'members' => Member::query()
                ->select(['id', 'user_id', 'member_code', 'phone', 'address', 'updated_at'])
                ->with('user:id,name,email')
                ->withCount('borrowings')
                ->withCount([
                    'borrowings as active_borrowings_count' => fn ($query) => $query->active(),
                ])
                ->withSum([
                    'borrowingItems as active_books_count' => fn ($query) => $query->whereHas(
                        'borrowing',
                        fn ($borrowingQuery) => $borrowingQuery->active()
                    ),
                ], 'qty')
                ->ordered()
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.members.create', [
            'generatedMemberCode' => Member::nextMemberCode(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => UserRole::Anggota,
            ]);

            Member::create([
                'user_id' => $user->getKey(),
                'member_code' => $validated['member_code'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        }, attempts: 5);

        return redirect()
            ->route('admin.members.index')
            ->with('status', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member): View
    {
        return view('admin.members.edit', [
            'member' => $member->load('user'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($member, $validated): void {
            $userAttributes = Arr::only($validated, ['name', 'email']);

            if (($validated['password'] ?? null) !== null) {
                $userAttributes['password'] = $validated['password'];
            }

            $member->user()->update($userAttributes);
            $member->update(Arr::only($validated, ['member_code', 'phone', 'address']));
        }, attempts: 5);

        return redirect()
            ->route('admin.members.index')
            ->with('status', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member): RedirectResponse
    {
        if ($member->borrowings()->exists()) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'Anggota yang sudah memiliki riwayat peminjaman tidak bisa dihapus.');
        }

        DB::transaction(function () use ($member): void {
            $member->user()->delete();
        }, attempts: 5);

        return redirect()
            ->route('admin.members.index')
            ->with('status', 'Anggota berhasil dihapus.');
    }
}
