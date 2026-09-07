@extends('layouts.app')

@section('title', 'Terms & Conditions and Privacy Policy - Fun 2 Win')

@section('content')
<div class="terms-modal-wrapper w-full min-h-[calc(100vh-2rem)] flex items-center justify-center p-2 sm:p-4 my-auto">
    <!-- Official Terms Modal Container -->
    <div class="terms-modal-card w-full max-w-3xl h-[82vh] sm:h-[86vh] max-h-[calc(100dvh-5.5rem)] sm:max-h-[820px] rounded-2xl sm:rounded-3xl bg-[#0d1322]/95 backdrop-blur-2xl border border-slate-700/80 shadow-[0_25px_60px_rgba(0,0,0,0.9),0_0_35px_rgba(245,158,11,0.08)] flex flex-col overflow-hidden animate-fadeIn">

        <!-- Top Modal Header -->
        <div class="px-5 sm:px-8 py-4 sm:py-5 bg-gradient-to-b from-[#111827] to-[#0d1322] border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-black/60 border border-amber-500/40 p-1 flex items-center justify-center shadow-lg shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-sm sm:text-lg font-bold font-royal text-amber-300 tracking-wide leading-tight">
                        Terms & Conditions and Privacy Policy
                    </h1>
                </div>
            </div>
        </div>

        <!-- Scrollable Legal Content Area -->
        <div class="flex-1 overflow-y-auto px-5 sm:px-8 py-5 text-slate-300 text-xs sm:text-sm leading-relaxed custom-scrollbar space-y-5" id="terms-scroll-body">
            
            <!-- Introductory Statement -->
            <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 text-slate-300 space-y-2">
                <p>
                    Welcome to <strong class="text-amber-300">Fun 2 Win Live Game</strong> (“Game”, “Service”, “we”, “our”, “us”). This combined Terms & Conditions and Privacy Policy governs your access to and use of our mobile application, website, and related services.
                </p>
                <p>
                    By accessing or using the Service, you agree to be bound by this document. If you do not agree, please discontinue use of the Service immediately.
                </p>
                <p class="text-[11px] text-slate-400">
                    This document is governed by and prepared in accordance with the laws applicable in India, including the Information Technology Act, 2000 and relevant rules thereunder.
                </p>
            </div>

            <!-- PART A - TERMS AND CONDITIONS -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="px-2.5 py-1 rounded-md bg-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider">PART A</span>
                    <h2 class="text-base sm:text-lg font-bold font-royal text-white">TERMS AND CONDITIONS</h2>
                </div>

                <!-- Section 1 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">1. Eligibility</h3>
                    <p class="mb-1">1.1 You must be 18 years of age or older to use the Fun 2 Win Live Game.</p>
                    <p class="mb-1">1.2 By using the Service, you represent and warrant that:</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>You are legally competent to enter into a binding agreement</li>
                        <li>You are not prohibited from using such services under applicable laws</li>
                        <li>You are accessing the Game from a jurisdiction where it is legally permitted</li>
                    </ul>
                </div>

                <!-- Section 2 -->
                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-amber-500/30">
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">2. Nature of the Game</h3>
                    <p class="mb-1">2.1 Fun 2 Win Live Game is strictly an amusement and entertainment-based game.</p>
                    <p class="mb-1">2.2 The Game does not involve real money, real cash winnings, or real-money gambling in any form.</p>
                    <p class="mb-1">2.3 Any coins, points, tokens, or virtual items used in the Game are purely virtual, have no real-world monetary value, and are intended solely for entertainment.</p>
                    <p class="mb-1">2.4 Users must not use, stake, wager, or associate any real money with gameplay, either directly or indirectly.</p>
                    <p class="mb-1">2.5 We shall not be responsible or liable for any real-money transactions, losses, disputes, or claims arising from users independently engaging in real-money involvement.</p>
                    <p>2.6 The Game is intended solely for fun and recreational purposes, and game outcomes do not result in real-world financial gain or loss.</p>
                </div>

                <!-- Section 3 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">3. User Account</h3>
                    <p class="mb-1">3.1 You may be required to create an account to access certain features.</p>
                    <p class="mb-1">3.2 You are responsible for maintaining the confidentiality of your account credentials and all activities under your account.</p>
                    <p class="mb-1">3.3 We reserve the right to suspend or terminate accounts that:</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>Provide false or misleading information</li>
                        <li>Violate these terms</li>
                        <li>Engage in fraudulent, abusive, or unlawful activity</li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">4. User Responsibilities</h3>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>Do not use the Game for illegal or unauthorized purposes</li>
                        <li>Do not attempt to manipulate, exploit, reverse-engineer, or interfere with the Game</li>
                        <li>Do not use bots, automation, or unfair practices</li>
                        <li>Do not harass or harm other users</li>
                    </ul>
                </div>

                <!-- Section 5 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">5. Internet Connectivity & Gameplay Responsibility</h3>
                    <p class="mb-1">5.1 Users must ensure they have a stable and proper internet connection while playing.</p>
                    <p class="mb-1">5.2 We are not responsible for gameplay disruption due to:</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>Poor internet connectivity</li>
                        <li>Automatic logouts</li>
                        <li>Network or device issues</li>
                    </ul>
                    <p class="mt-1 mb-1">5.3 In such cases, game results, credits, or settlements will be considered final strictly as per the Game History / Player History recorded in the system.</p>
                    <p>5.4 No claims beyond official in-game records will be entertained.</p>
                </div>

                <!-- Section 6 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">6. Betting Window & Action Timing</h3>
                    <p class="mb-1">6.1 Users must place their chips, coins, or virtual bets within the active betting window.</p>
                    <p class="mb-1">6.2 Once the betting window is closed, no actions can be performed.</p>
                    <p class="mb-1">6.3 Failure to place bets on time—due to delay, hesitation, technical issues, or internet problems—shall not be the responsibility of the organization.</p>
                    <p>6.4 Users acknowledge that timely gameplay actions are entirely their responsibility.</p>
                </div>

                <!-- Section 7 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">7. Payments, Virtual Items, and Purchases</h3>
                    <p class="mb-1">7.1 The Game may offer virtual items or in-app purchases.</p>
                    <p class="mb-1">7.2 All purchases are final and non-refundable, unless required by law.</p>
                    <p class="mb-1">7.3 Virtual items cannot be exchanged for cash or transferred outside the Game.</p>
                    <p>7.4 Payments are processed via third-party gateways. We are not responsible for gateway failures.</p>
                </div>

                <!-- Section 8 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">8. Fair Play and Anti-Fraud</h3>
                    <p>Any attempt to cheat, collude, exploit bugs, or manipulate gameplay may result in immediate suspension or permanent termination without notice.</p>
                </div>

                <!-- Section 9 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">9. Intellectual Property</h3>
                    <p>All content, software, logos, and designs belong to or are licensed to Fun 2 Win Live Game. Unauthorized use is strictly prohibited.</p>
                </div>

                <!-- Section 10 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">10. Disclaimer & Limitation of Liability</h3>
                    <p class="mb-1">The Service is provided on an “as is” and “as available” basis.</p>
                    <p class="mb-1">We are not liable for indirect, incidental, or consequential damages.</p>
                    <p>Your sole remedy is to discontinue use of the Game.</p>
                </div>

                <!-- Section 11 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">11. Suspension and Termination</h3>
                    <p>We reserve the right to suspend or terminate access at any time for violations of these terms or applicable laws.</p>
                </div>

                <!-- Section 12 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">12. Indemnification</h3>
                    <p>You agree to indemnify and hold harmless Fun 2 Win Live Game from any claims arising from your use of the Service or violation of this document.</p>
                </div>

                <!-- Section 13 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">13. Governing Law and Jurisdiction</h3>
                    <p>This document shall be governed by the laws of India. Courts in India shall have exclusive jurisdiction.</p>
                </div>
            </div>

            <!-- PART B - PRIVACY POLICY -->
            <div class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-2">
                    <span class="px-2.5 py-1 rounded-md bg-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider">PART B</span>
                    <h2 class="text-base sm:text-lg font-bold font-royal text-white">PRIVACY POLICY</h2>
                </div>

                <!-- Section 14 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">14. Information We Collect</h3>
                    <p class="font-semibold text-slate-200 text-xs mb-0.5">14.1 Personal Information</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs mb-2">
                        <li>Name or username</li>
                        <li>Contact details / Mobile number</li>
                        <li>Date of birth / age confirmation</li>
                        <li>Support or feedback information</li>
                    </ul>
                    <p class="font-semibold text-slate-200 text-xs mb-0.5">14.2 Non-Personal Information</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs mb-2">
                        <li>Device type, OS, identifiers</li>
                        <li>IP address</li>
                        <li>App usage and interaction logs</li>
                        <li>Game statistics and preferences</li>
                    </ul>
                    <p class="font-semibold text-slate-200 text-xs mb-0.5">14.3 Payment Information</p>
                    <p class="text-slate-400 text-xs">Payment processing is handled by third-party gateways. We do not store sensitive payment data.</p>
                </div>

                <!-- Section 15 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">15. How We Use Information</h3>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>Operate and improve the Game</li>
                        <li>Manage accounts and gameplay</li>
                        <li>Communicate updates and support</li>
                        <li>Prevent fraud and ensure security</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </div>

                <!-- Section 16 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">16. Sharing of Information</h3>
                    <p class="mb-1">We do not sell personal data. Information may be shared only with:</p>
                    <ul class="list-disc list-inside pl-2 space-y-0.5 text-slate-400 text-xs">
                        <li>Service providers under confidentiality</li>
                        <li>Legal authorities when required</li>
                        <li>Business entities in case of mergers or acquisitions</li>
                    </ul>
                </div>

                <!-- Section 17 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">17. Data Security</h3>
                    <p>We use reasonable safeguards to protect user data. However, no system is completely secure.</p>
                </div>

                <!-- Section 18 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">18. Data Retention</h3>
                    <p>Data is retained only as long as necessary or as required by law.</p>
                </div>

                <!-- Section 19 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">19. Children’s Privacy</h3>
                    <p>The Game is intended for users 18 years and above. We do not knowingly collect data from minors.</p>
                </div>

                <!-- Section 20 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">20. Third-Party Services</h3>
                    <p>We are not responsible for the privacy practices of third-party services linked through the Game.</p>
                </div>

                <!-- Section 21 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">21. User Rights</h3>
                    <p>Users may request access, correction, or deletion of their personal data, subject to legal requirements.</p>
                </div>

                <!-- Section 22 -->
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">22. Changes to This Document</h3>
                    <p>We may update this document from time to time. Continued use constitutes acceptance of updates.</p>
                </div>

                <!-- Section 23 -->
                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                    <h3 class="text-xs sm:text-sm font-bold text-amber-300 uppercase tracking-wider mb-1">23. Contact Information</h3>
                    <p class="mb-1 text-slate-300">For any questions or concerns:</p>
                    <p class="font-bold text-amber-400 text-sm">🎮 Game: Fun 2 Win Live Game</p>
                    <p class="text-xs text-slate-400 mt-1">Exclusive Live Gaming Club • Fun 2 Win</p>
                </div>
            </div>
        </div>

        <!-- Bottom Fixed Action Controls (Decline & Accept) -->
        <div class="terms-action-bar px-5 sm:px-8 py-3.5 sm:py-4 bg-[#090d16] border-t border-slate-800 flex flex-row items-center justify-between gap-3 sm:gap-4 shrink-0 shadow-2xl">
            @auth
            <!-- Decline Button Form -->
            <form action="{{ route('terms.decline') }}" method="POST" class="flex-1 m-0">
                @csrf
                <button 
                    type="submit" 
                    id="terms-decline-btn"
                    class="w-full py-3 sm:py-3.5 px-4 rounded-xl font-bold text-xs sm:text-sm uppercase tracking-wider text-white bg-[#dc2626] hover:bg-[#b91c1c] active:scale-95 transition duration-150 shadow-lg shadow-red-600/30 flex items-center justify-center gap-1.5 cursor-pointer"
                >
                    <span>✕</span>
                    <span>Decline</span>
                </button>
            </form>

            <!-- Accept Button Form -->
            <form action="{{ route('terms.accept') }}" method="POST" class="flex-1 m-0">
                @csrf
                <button 
                    type="submit" 
                    id="terms-accept-btn"
                    class="w-full py-3 sm:py-3.5 px-4 rounded-xl font-black text-xs sm:text-sm uppercase tracking-widest text-slate-950 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 hover:from-amber-400 hover:to-yellow-300 active:scale-95 transition duration-150 shadow-lg shadow-amber-500/35 flex items-center justify-center gap-1.5 cursor-pointer"
                >
                    <span>✓</span>
                    <span>Accept</span>
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="w-full py-3 sm:py-3.5 px-4 rounded-xl font-black text-xs sm:text-sm uppercase tracking-widest text-slate-950 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 hover:from-amber-400 hover:to-yellow-300 transition duration-150 shadow-lg shadow-amber-500/35 flex items-center justify-center gap-1.5 cursor-pointer text-center text-decoration-none">
                <span>←</span>
                <span>Back to Login</span>
            </a>
            @endauth
        </div>

    </div>
</div>

<style>
@keyframes fadeInModal {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
    animation: fadeInModal 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.6);
    border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(245, 158, 11, 0.35);
    border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(245, 158, 11, 0.6);
}

/* Mobile website only: elevate modal and buttons so they are never cut off by browser navigation/gesture bars */
@media (max-width: 640px) {
    .terms-modal-wrapper {
        align-items: flex-start !important;
        padding-top: 0.5rem !important;
        padding-bottom: 2.25rem !important;
        min-height: 100dvh !important;
    }
    .terms-modal-card {
        height: calc(100dvh - 5.5rem) !important;
        max-height: calc(100dvh - 5.5rem) !important;
        margin-bottom: 1.75rem !important;
    }
    .terms-action-bar {
        padding-top: 0.75rem !important;
        padding-bottom: 1.25rem !important;
    }
}
</style>
@endsection
