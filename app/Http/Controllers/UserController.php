<?php

namespace App\Http\Controllers;

use App\Actions\User\StoreUserAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(private UserRepository $users) {}

    public function index(): View
    {
        return view('users.index');
    }

    public function data(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->users->dataTableQuery())
            ->editColumn('created_at', fn (User $u): string => $u->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y'))
            ->addColumn('role', fn (User $u): string => e($u->roles->map(fn ($r) => str($r->name)->headline())->implode(', ') ?: '-'))
            ->addColumn('status', fn (User $u): string => $this->statusBadge($u))
            ->addColumn('aksi', fn (User $u): string => $this->actionButtons($u))
            ->rawColumns(['status', 'aksi'])
            ->toJson();
    }

    public function create(): View
    {
        return view('users.create', ['roles' => $this->roleOptions()]);
    }

    public function store(StoreUserRequest $request, StoreUserAction $action): RedirectResponse
    {
        $action->execute($request->userData());

        return redirect()->route('user.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->roleOptions(),
            'currentRole' => $user->roles->first()?->name,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $action->execute($user, $request->userData());

        return redirect()->route('user.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->is($request->user())) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        $this->users->delete($user);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus.']);
    }

    public function toggle(Request $request, User $user): JsonResponse
    {
        if ($user->is($request->user())) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menonaktifkan akun sendiri.'], 422);
        }

        $this->users->update($user, ['is_active' => ! $user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Pengguna diaktifkan.' : 'Pengguna dinonaktifkan.',
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $this->users->update($user, ['password' => $data['password']]);

        return response()->json(['success' => true, 'message' => 'Kata sandi berhasil direset.']);
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        return Role::query()->where('name', '!=', 'customer')->orderBy('name')->pluck('name')
            ->mapWithKeys(fn (string $name): array => [$name => (string) str($name)->headline()])
            ->all();
    }

    private function statusBadge(User $user): string
    {
        return $user->is_active
            ? '<span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Aktif</span>'
            : '<span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">Nonaktif</span>';
    }

    private function actionButtons(User $user): string
    {
        $editUrl = route('user.edit', $user);
        $toggleLabel = $user->is_active ? 'Nonaktifkan' : 'Aktifkan';

        return <<<HTML
            <div class="flex items-center justify-end gap-2">
                <a href="{$editUrl}" class="btn-act btn-act--edit"><i data-lucide="pencil"></i> Edit</a>
                <button type="button" class="btn-reset btn-act btn-act--reset" data-id="{$user->id}"><i data-lucide="key-round"></i> Reset</button>
                <button type="button" class="btn-toggle btn-act btn-act--neutral" data-id="{$user->id}"><i data-lucide="power"></i> {$toggleLabel}</button>
                <button type="button" class="btn-delete btn-act btn-act--delete" data-id="{$user->id}"><i data-lucide="trash-2"></i> Hapus</button>
            </div>
        HTML;
    }
}
