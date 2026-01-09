@extends('layouts.blank')

@section('title', 'Welcome to '.config('app.name', 'InboxPilot'))
        
@section('content')
<div class="w-full max-w-6xl mx-auto">
    <!-- Hero Section -->
    <div class="text-center mb-20 lg:mb-32">
        <h1 class="text-5xl lg:text-7xl font-bold mb-6 text-white leading-tight">
            Master Your Inbox with
            <span class="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">InboxPilot</span>
        </h1>
        <p class="text-xl lg:text-2xl text-gray-400 mb-10 max-w-3xl mx-auto leading-relaxed">
            Take control of your email workflow. Smart automation, intelligent filtering, and seamless management—all in one place.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            @guest
                <a href="{{ route('register') }}" 
                   class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                    Get Started Free
                </a>
                <a href="{{ route('login') }}" 
                   class="px-8 py-4 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-lg border border-gray-700 transition-all duration-300">
                    Sign In
                </a>
            @else
                <a href="{{ url('/dashboard') }}" 
                   class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                    Go to Dashboard
                </a>
            @endguest
        </div>
    </div>

    <!-- Features Section -->
    <div class="grid md:grid-cols-3 gap-8 mb-20 lg:mb-32">
        <!-- Feature 1 -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 hover:border-gray-700 transition-all duration-300 hover:shadow-xl">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-3">Smart Automation</h3>
            <p class="text-gray-400 leading-relaxed">
                Automate repetitive tasks and let AI handle email sorting, prioritization, and responses.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 hover:border-gray-700 transition-all duration-300 hover:shadow-xl">
            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-3">Intelligent Filtering</h3>
            <p class="text-gray-400 leading-relaxed">
                Advanced filters that learn from your behavior to keep your inbox clean and organized.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 hover:border-gray-700 transition-all duration-300 hover:shadow-xl">
            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-3">Secure & Private</h3>
            <p class="text-gray-400 leading-relaxed">
                Enterprise-grade security with end-to-end encryption to keep your data safe and private.
            </p>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 border border-gray-800 rounded-2xl p-12 mb-20 lg:mb-32">
        <div class="grid md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold text-transparent bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text mb-2">10M+</div>
                <div class="text-gray-400">Emails Processed</div>
            </div>
            <div>
                <div class="text-5xl font-bold text-transparent bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text mb-2">50K+</div>
                <div class="text-gray-400">Active Users</div>
            </div>
            <div>
                <div class="text-5xl font-bold text-transparent bg-gradient-to-r from-green-400 to-teal-500 bg-clip-text mb-2">99.9%</div>
                <div class="text-gray-400">Uptime</div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="text-center bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-12 lg:p-16">
        <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">
            Ready to Transform Your Inbox?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Join thousands of professionals who've already streamlined their email workflow with InboxPilot.
        </p>
        @guest
            <a href="{{ route('register') }}" 
               class="inline-block px-10 py-4 bg-white text-purple-700 font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                Start Your Free Trial
            </a>
        @else
            <a href="{{ url('/dashboard') }}" 
               class="inline-block px-10 py-4 bg-white text-purple-700 font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                Go to Your Dashboard
            </a>
        @endguest
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Add subtle animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.bg-gray-900, .bg-gradient-to-r').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
@endpush