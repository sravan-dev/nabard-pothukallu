<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$pageTitle = "Login";
$error = '';
$loginEmail = '';
$showLoginModal = true;
$tribalHeroImage = 't1.jpg';
$tribalChildrenImage = 't1.jpg';
$tribalCommunityImage = 't1.jpg';
$jssLogo = 'jss.png';
$liveStats = [
    'families' => 400,
    'nagars' => 20,
    'staff' => 7,
    'pending_targets' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $loginEmail = $email;
    $password = $_POST['password'] ?? '';
    $showLoginModal = true;

    if ($email === '' || $password === '') {
        $error = "Please fill in all fields.";
    } elseif (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Security validation failed. Please try again.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            redirect('dashboard.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="relative min-h-screen overflow-hidden landing-bg">
    <div class="absolute inset-0 landing-grain"></div>
    <div class="absolute -top-24 -left-16 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
    <div class="absolute top-24 right-0 h-96 w-96 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-emerald-300/20 blur-3xl"></div>

    <div class="relative z-10 flex min-h-screen flex-col px-4 py-6 sm:px-8 lg:px-12">
        <header class="mx-auto w-full max-w-7xl">
            <div class="glass rounded-2xl px-4 py-4 sm:px-6">
                <div class="grid items-center gap-4 text-center sm:grid-cols-[1fr_auto_1fr]">
                    <div class="px-2 py-2 sm:px-4">
                        <p class="text-2xl font-bold tracking-wide text-emerald-100">TRIBES</p>
                        <p class="text-xs text-slate-300">Pothukal Grama Panchayath</p>
                    </div>
                    <div class="mx-auto flex w-full max-w-sm items-center justify-center gap-4 px-2 py-1">
                        <span class="hidden h-16 w-px bg-white/35 sm:block"></span>
                        <div class="text-center">
                            <img src="logo.png" alt="NABARD Logo" class="mx-auto h-16 w-16 rounded-full bg-white/90 p-1.5 shadow-lg shadow-emerald-900/30">
                            <p class="mt-1 text-2xl font-semibold tracking-[0.08em] text-emerald-100">NABARD</p>
                        </div>
                        <span class="hidden h-16 w-px bg-white/35 sm:block"></span>
                    </div>
                    <div class="px-2 py-2 sm:px-4">
                        <div class="flex items-center justify-center gap-3 sm:justify-end">
                            <img src="<?php echo htmlspecialchars($jssLogo); ?>" alt="Jan Shikshan Sansthan Malappuram Logo" class="h-12 w-12 rounded-full bg-white/90 p-1 shadow-lg shadow-indigo-900/30">
                            <p class="text-lg font-bold leading-tight text-indigo-100 sm:text-right">Jan Shikshan Sansthan Malappuram</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-8 py-8 lg:grid-cols-[1.05fr_0.95fr] lg:py-12">
            <section>
                <p class="text-xl font-medium text-emerald-200">Welcome to the</p>
                <h1 class="mt-2 text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                    NABARD Project
                    <span class="hero-gradient">Management Portal</span>
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-slate-200">
                    Manage and track NABARD project activities, member records, family details, and meetings from one central dashboard.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <button type="button" data-open-login class="rounded-full border border-cyan-200/60 bg-cyan-400/30 px-8 py-3 text-xl font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 hover:bg-cyan-300/40">
                        Login
                    </button>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="tribal-card col-span-2 sm:col-span-2">
                        <img src="<?php echo htmlspecialchars($tribalHeroImage); ?>" alt="Tribal village scene" class="h-full w-full object-cover">
                    </div>
                    <div class="tribal-card">
                        <img src="<?php echo htmlspecialchars($tribalChildrenImage); ?>" alt="Tribal children" class="h-full w-full object-cover">
                    </div>
                </div>
            </section>

            <section class="glass relative overflow-hidden rounded-3xl border border-cyan-200/25 p-5 shadow-2xl shadow-cyan-900/50">
                <div class="absolute -top-16 right-0 h-40 w-40 rounded-full bg-cyan-300/20 blur-2xl"></div>
                <div class="relative">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm tracking-[0.2em] text-cyan-200">NABARD LIVE</p>
                        <div class="rounded-full bg-emerald-400/25 px-3 py-1 text-xs font-semibold text-emerald-200">ONLINE</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-white/15 bg-slate-900/60 p-4">
                            <p class="text-xs text-slate-400">Families</p>
                            <p class="mt-1 text-3xl font-bold text-white"><?php echo (int)$liveStats['families']; ?></p>
                        </div>
                        <div class="rounded-xl border border-white/15 bg-slate-900/60 p-4">
                            <p class="text-xs text-slate-400">Nagars</p>
                            <p class="mt-1 text-3xl font-bold text-white"><?php echo (int)$liveStats['nagars']; ?></p>
                        </div>
                        <div class="rounded-xl border border-white/15 bg-slate-900/60 p-4">
                            <p class="text-xs text-slate-400">Staff</p>
                            <p class="mt-1 text-3xl font-bold text-white"><?php echo (int)$liveStats['staff']; ?></p>
                        </div>
                        <div class="rounded-xl border border-red-300/30 bg-red-500/10 p-4">
                            <p class="text-xs text-red-200">Pending Targets</p>
                            <p class="mt-1 text-3xl font-bold text-red-100"><?php echo (int)$liveStats['pending_targets']; ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<div id="loginModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4 opacity-0 transition-opacity duration-300">
    <div id="loginPanel" class="glass w-full max-w-5xl scale-95 rounded-2xl border border-white/25 shadow-2xl transition-transform duration-300">
        <div class="grid min-h-[560px] overflow-hidden rounded-2xl md:grid-cols-[1.05fr_0.95fr]">
            <div class="relative hidden md:block">
                <img src="<?php echo htmlspecialchars($tribalHeroImage); ?>" alt="Tribal heritage village" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900/20 via-slate-900/45 to-slate-900/85"></div>
                <div class="absolute left-6 right-6 top-6">
                    <p class="text-xs tracking-[0.25em] text-cyan-200">COMMUNITY FIRST</p>
                    <h3 class="mt-2 text-2xl font-bold text-white">NABARD Tribal Development Portal</h3>
                </div>
                <div class="absolute inset-x-6 bottom-6 grid grid-cols-2 gap-3">
                    <div class="overflow-hidden rounded-xl border border-white/30 bg-black/30">
                        <img src="<?php echo htmlspecialchars($tribalChildrenImage); ?>" alt="Tribal children smiling" class="h-24 w-full object-cover">
                    </div>
                    <div class="overflow-hidden rounded-xl border border-white/30 bg-black/30">
                        <img src="<?php echo htmlspecialchars($tribalCommunityImage); ?>" alt="Tribal community culture" class="h-24 w-full object-cover">
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="mb-5 overflow-hidden rounded-xl border border-white/20 md:hidden">
                    <img src="<?php echo htmlspecialchars($tribalHeroImage); ?>" alt="Tribal heritage village" class="h-40 w-full object-cover">
                </div>
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-cyan-200">Secure Access</p>
                        <h2 class="mt-1 text-2xl font-bold text-white">Login to Continue</h2>
                    </div>
                    <button type="button" id="closeLoginModal" class="rounded-md p-1 text-slate-300 hover:bg-white/10 hover:text-white" aria-label="Close login modal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="mb-4 rounded-lg border border-red-400/40 bg-red-500/15 px-3 py-2 text-sm text-red-100">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-200">Email</label>
                        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($loginEmail); ?>" class="w-full rounded-lg border border-white/20 bg-slate-900/70 px-4 py-3 text-white placeholder-slate-400 outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/40" placeholder="Enter your email">
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-200">Password</label>
                        <input type="password" name="password" id="password" required class="w-full rounded-lg border border-white/20 bg-slate-900/70 px-4 py-3 text-white placeholder-slate-400 outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/40" placeholder="Enter your password">
                    </div>

                    <button type="submit" class="w-full rounded-lg border border-cyan-200/60 bg-cyan-400/35 px-4 py-3 text-base font-semibold text-white transition hover:bg-cyan-300/45">
                        Sign In
                    </button>
                    <p class="text-xs text-slate-300">Images are representative tribal community visuals.</p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .landing-bg {
        background:
            radial-gradient(circle at 18% 18%, rgba(83, 221, 255, 0.26), transparent 38%),
            radial-gradient(circle at 84% 22%, rgba(197, 84, 255, 0.23), transparent 42%),
            radial-gradient(circle at 50% 85%, rgba(37, 255, 179, 0.2), transparent 40%),
            linear-gradient(150deg, #061738 0%, #081a45 42%, #151047 100%);
    }

    .landing-grain {
        background-image: radial-gradient(rgba(255, 255, 255, 0.09) 1px, transparent 1px);
        background-size: 3px 3px;
        opacity: 0.2;
        pointer-events: none;
    }

    .hero-gradient {
        background: linear-gradient(90deg, #d1fae5 0%, #c4b5fd 40%, #67e8f9 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .tribal-card {
        overflow: hidden;
        border-radius: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.25);
        min-height: 130px;
    }

    .tribal-card img {
        transition: transform 0.35s ease;
    }

    .tribal-card:hover img {
        transform: scale(1.06);
    }
</style>

<script>
    (() => {
        const modal = document.getElementById('loginModal');
        const panel = document.getElementById('loginPanel');
        const closeBtn = document.getElementById('closeLoginModal');
        const openBtns = document.querySelectorAll('[data-open-login]');
        const shouldAutoOpen = <?php echo $showLoginModal ? 'true' : 'false'; ?>;

        function openLoginModal() {
            if (!modal || !panel) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                panel.classList.remove('scale-95');
                panel.classList.add('scale-100');
            });
        }

        function closeLoginModal() {
            if (!modal || !panel) return;
            modal.classList.add('opacity-0');
            panel.classList.remove('scale-100');
            panel.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 250);
        }

        openBtns.forEach((btn) => {
            btn.addEventListener('click', openLoginModal);
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeLoginModal);
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeLoginModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeLoginModal();
            }
        });

        if (shouldAutoOpen) {
            openLoginModal();
        }
    })();
</script>

<?php require_once 'includes/footer.php'; ?>
