@extends('layouts.app')
@section('title', 'Manage Users — Admin')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">Manage Users</h1>
   <a href="{{ route('admin.dashboard') }}" class="text-xs text-neutral-400 hover:text-black transition-colors">← Admin
      Dashboard</a>
</div>

<div class="border border-neutral-200 rounded-lg overflow-hidden">
   <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200">
         <thead>
            <tr class="border-b border-neutral-200">
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">User</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Devices
               </th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Role</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Joined
               </th>
               <th class="px-6 py-3 text-right text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Actions
               </th>
            </tr>
         </thead>
         <tbody class="divide-y divide-neutral-100">
            @foreach($users as $user)
            <tr class="hover:bg-neutral-50 transition-colors">
               <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-black">{{ $user->name }}</p>
                  <p class="text-xs text-neutral-400">{{ $user->email }}</p>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                  {{ $user->devices_count }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap">
                  <span
                     class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $user->isAdmin() ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
                     {{ $user->isAdmin() ? 'Admin' : 'User' }}
                  </span>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-400">
                  {{ $user->created_at->format('M d, Y') }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                  <form method="POST" action="{{ route('admin.toggle-admin', $user) }}" class="inline">
                     @csrf @method('PATCH')
                     <button type="submit"
                        class="text-neutral-500 hover:text-black transition-colors text-xs underline underline-offset-2">
                        {{ $user->isAdmin() ? 'Remove Admin' : 'Make Admin' }}
                     </button>
                  </form>
                  @if(!$user->isAdmin())
                  <form method="POST" action="{{ route('admin.delete-user', $user) }}" class="inline"
                     onsubmit="return confirm('Delete this user and all their devices?')">
                     @csrf @method('DELETE')
                     <button type="submit"
                        class="text-neutral-400 hover:text-black transition-colors text-xs underline underline-offset-2 ml-2">Delete</button>
                  </form>
                  @endif
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
</div>

<div class="mt-4">
   {{ $users->links() }}
</div>
@endsection