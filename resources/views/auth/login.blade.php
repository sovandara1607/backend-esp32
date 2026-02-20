@extends('layouts.app')
@section('title', 'Login — IoT Monitor')

@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
   <div class="w-full max-w-sm">
      <div class="border border-neutral-200 rounded-lg p-8">
         <div class="text-center mb-8">
            <svg class="w-10 h-10 mx-auto text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
               <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c-2.5-2.5-5-3-7-1s-1.5 4.5 1 7m6-6c2.5-2.5 3-5 1-7s-4.5-1.5-7 1m6 6c2.5 2.5 5 3 7 1s1.5-4.5-1-7m-6 6c-2.5 2.5-3 5-1 7s4.5 1.5 7-1" />
               <circle cx="12" cy="12" r="1.5" fill="currentColor" />
            </svg>
            <h2 class="mt-4 text-lg font-semibold text-black">Welcome back</h2>
            <p class="mt-1 text-xs text-neutral-400">Sign in to your IoT Monitor account</p>
         </div>

         <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
               <label for="email" class="block text-xs font-medium text-neutral-500 mb-1">Email address</label>
               <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                  class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none transition-colors">
            </div>

            <div>
               <label for="password" class="block text-xs font-medium text-neutral-500 mb-1">Password</label>
               <input id="password" type="password" name="password" required
                  class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none transition-colors">
            </div>

            <div class="flex items-center">
               <label class="flex items-center">
                  <input type="checkbox" name="remember" class="rounded border-neutral-300 text-black focus:ring-0">
                  <span class="ml-2 text-xs text-neutral-500">Remember me</span>
               </label>
            </div>

            <button type="submit"
               class="w-full py-2 px-4 rounded-lg text-sm font-medium text-white bg-black hover:bg-neutral-800 transition-colors">
               Sign in
            </button>
         </form>

         <p class="mt-6 text-center text-xs text-neutral-400">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-black hover:underline">Register</a>
         </p>
      </div>
   </div>
</div>
@endsection