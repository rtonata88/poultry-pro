<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use WithPagination;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];
    public $editingId = null;
    public $showForm = false;
    public $search = '';

    public function mount()
    {
        //
    }

    public function create()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'selectedRoles', 'editingId']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->editingId,
            'selectedRoles' => 'array',
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            if ($this->password) {
                $rules['password'] = 'string|min:8|confirmed';
            }
        }

        $this->validate($rules);

        // Convert role IDs to role names
        $roleNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            $user->update($userData);
            $user->syncRoles($roleNames);
            session()->flash('status', 'User updated successfully.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->syncRoles($roleNames);
            session()->flash('status', 'User created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $user = User::with('roles')->findOrFail($id);
        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->showForm = true;
    }

    public function delete($id): void
    {
        // Prevent deleting current user
        if ($id == auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('status', 'User deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'selectedRoles', 'editingId', 'showForm']);
    }

    public function render()
    {
        $query = User::withCount('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.settings.users', [
            'users' => $query->orderBy('name')->paginate(10),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
