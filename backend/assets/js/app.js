/**
 * Food Rescue - Core Application Logic
 * Handles: Auth State, API Calls, UI Transitions
 */

const CONFIG = {
    BASE_API: ''
};

const App = {
    state: {
        user: JSON.parse(localStorage.getItem('fr_user')) || null,
        currentView: 'hero',
        currentTab: null
    },

    init() {
        this.cacheDOM();
        this.bindEvents();
        if (this.state.user) {
            this.state.currentView = 'dashboard';
            const defaultTabs = { 'ADMIN': 'admin_overview', 'DONOR': 'donor_overview', 'NGO': 'ngo_overview', 'VOLUNTEER': 'vol_overview' };
            this.state.currentTab = defaultTabs[this.state.user.role];
        }
        this.render();
        console.log('Food Rescue Web Initialized');
    },

    cacheDOM() {
        this.app = document.getElementById('app');
        this.nav = document.getElementById('main-nav');
    },

    bindEvents() {
        window.addEventListener('popstate', () => this.render());
        document.addEventListener('click', (e) => {
            if (e.target.dataset.link) {
                e.preventDefault();
                this.navigateTo(e.target.dataset.link);
            }
        });
    },

    navigateTo(view) {
        this.state.currentView = view;
        this.render();
    },

    switchTab(tab) {
        this.state.currentTab = tab;
        this.renderDashboard();
    },

    async register(data) {
        try {
            const res = await this.apiPost('/auth/register.php', data);
            if (res.success) {
                this.showToast('Account created! Please login.', 'success');
                this.navigateTo('login');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (err) {
            this.showToast('Server connection failed', 'error');
        }
    },

    async login(data) {
        try {
            const res = await this.apiPost('/auth/login.php', data);
            if (res.success) {
                this.state.user = res.data;
                const defaultTabs = { 'ADMIN': 'admin_overview', 'DONOR': 'donor_overview', 'NGO': 'ngo_overview', 'VOLUNTEER': 'vol_overview' };
                this.state.currentTab = defaultTabs[res.data.role];
                localStorage.setItem('fr_user', JSON.stringify(res.data));
                this.showToast(`Welcome back, ${res.data.name}!`, 'success');
                this.navigateTo('dashboard');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (err) {
            this.showToast('Login failed. Check credentials.', 'error');
        }
    },

    logout() {
        this.state.user = null;
        localStorage.removeItem('fr_user');
        this.navigateTo('hero');
    },

    async apiPost(endpoint, data) {
        const response = await fetch(`${CONFIG.BASE_API}${endpoint}`, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' }
        });
        return response.json();
    },

    async apiGet(endpoint) {
        const response = await fetch(`${CONFIG.BASE_API}${endpoint}`);
        return response.json();
    },

    showToast(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast glass animate-fade`;
        toast.innerHTML = `
            <span style="color: ${type === 'success' ? 'var(--primary)' : '#ef4444'}">
                ${type === 'success' ? '●' : '■'}
            </span>
            <span>${msg}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    },

    render() {
        const { currentView, user } = this.state;

        if (user && (currentView === 'hero' || currentView === 'login' || currentView === 'register')) {
            this.navigateTo('dashboard');
            return;
        }

        if (currentView === 'dashboard' && user) {
            this.nav.style.display = 'none';
            this.renderDashboard();
            return;
        }

        if (currentView === 'login' || currentView === 'register') {
            this.nav.style.display = 'none';
        } else {
            this.nav.style.display = 'block';
        }
        // Navigation Bar State
        this.nav.innerHTML = `
            <div class="container" style="display:flex; justify-content:space-between; align-items:center; width:100%; height:100%">
                <div class="logo gradient-text" data-link="${user ? 'dashboard' : 'hero'}" style="cursor:pointer; font-size:1.6rem; font-weight:900; letter-spacing:-0.05em">
                    FOODRESCUE
                </div>
                <div class="nav-links">
                    ${user ? `
                        <span style="margin-right:20px; color:var(--text-muted)">Hi, ${user.name}</span>
                        <button class="btn btn-primary" data-link="dashboard">Dashboard</button>
                        <button class="btn btn-outline" onclick="App.logout()" style="margin-left:10px">Logout</button>
                    ` : `
                        <button class="btn btn-outline" data-link="login">Login</button>
                        <button class="btn btn-primary" data-link="register" style="margin-left:15px">Get Started</button>
                    `}
                </div>
            </div>
        `;

        // Page Router
        switch (currentView) {
            case 'hero': this.renderHero(); break;
            case 'login': this.renderLogin(); break;
            case 'register': this.renderRegister(); break;
            case 'dashboard': this.renderDashboard(); break;
        }
    },

    renderHero() {
        this.app.innerHTML = `
            <!-- Hero Section -->
            <section class="container hero animate-fade">
                <div class="hero-content">
                    <h1>Eliminating <span class="gradient-text">Food Waste</span> Through Technology</h1>
                    <p>
                        A real-time logistics ecosystem connecting surplus resources to those who need them most. We bridge the gap between abundance and scarcity with precision.
                    </p>
                    <div style="display: flex; gap: 16px">
                        <button class="btn btn-primary" data-link="register"><i class="fa-solid fa-user-plus"></i> Register Now</button>
                        <button class="btn btn-outline" data-link="login"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/hero_banner.png" alt="Rescue Ecosystem">
                </div>
            </section>

            <!-- Features Section -->
            <section class="section-padding glass" style="margin-top: 100px; border-radius:60px 60px 0 0">
                <div class="container">
                    <div class="text-center" style="margin-bottom: 80px">
                        <h2 style="font-size: 3rem; margin-bottom: 16px; font-weight:800; letter-spacing:-0.03em">The Zero-Waste Infrastructure</h2>
                        <p style="color: var(--text-dim); font-size:1.1rem; max-width:600px; margin:0 auto">Empowering communities with professional tools for efficient food redistribution.</p>
                    </div>
                    <div class="feature-grid">
                        <div class="glass-card">
                            <div class="feature-icon">🛡️</div>
                            <h3 style="margin-bottom:12px">Secure Network</h3>
                            <p style="color:var(--text-dim); font-size:0.95rem">Multi-layer verification for all donors and NGOs ensuring a trusted chain of custody.</p>
                        </div>
                        <div class="glass-card">
                            <div class="feature-icon">⚡</div>
                            <h3 style="margin-bottom:12px">Instant Intelligence</h3>
                            <p style="color:var(--text-dim); font-size:0.95rem">Predictive notifications and real-time alerts minimize transit time for fresh perishables.</p>
                        </div>
                        <div class="glass-card">
                            <div class="feature-icon">📍</div>
                            <h3 style="margin-bottom:12px">Advanced Logistics</h3>
                            <p style="color:var(--text-dim); font-size:0.95rem">Hyper-local matching algorithms connect surplus to the nearest available rescue unit.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="section-padding container animate-fade">
                <div class="stats-banner">
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">15,000+</h3>
                        <p>Meals Delivered</p>
                    </div>
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">920</h3>
                        <p>Certified Donors</p>
                    </div>
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">120+</h3>
                        <p>NGO Partners</p>
                    </div>
                </div>
            </section>

            <!-- How it Works -->
            <section class="section-padding">
                <div class="container">
                    <div class="text-center" style="margin-bottom: 80px">
                        <h2 style="font-size: 3rem; margin-bottom: 16px; font-weight:800; letter-spacing:-0.03em">The Rescue Protocol</h2>
                        <p style="color: var(--text-dim); font-size:1.1rem">A streamlined 3-step synchronization for maximum impact.</p>
                    </div>
                    <div class="steps-container">
                        <div class="step-card animate-fade">
                            <div class="step-number">01</div>
                            <div class="glass-card" style="width: 100%">
                                <h3 style="margin-bottom:12px">Broadcasting</h3>
                                <p style="color:var(--text-dim)">Donors log surplus inventory with precise quantity and expiration telemetry.</p>
                            </div>
                        </div>
                        <div class="step-card animate-fade" style="margin-left:auto">
                            <div class="step-number">02</div>
                            <div class="glass-card" style="width: 100%">
                                <h3 style="margin-bottom:12px">Coordination</h3>
                                <p style="color:var(--text-dim)">NGOs and Volunteers receive high-priority alerts tailored to their proximity and capacity.</p>
                            </div>
                        </div>
                        <div class="step-card animate-fade">
                            <div class="step-number">03</div>
                            <div class="glass-card" style="width: 100%">
                                <h3 style="margin-bottom:12px">Fulfillment</h3>
                                <p style="color:var(--text-dim)">Efficient pick-up and last-mile delivery ensure food reaches hungry hearts while fresh.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `;
    },
    _injectAuthStyles() {
        if (document.getElementById('auth-styles')) return;
        const style = document.createElement('style');
        style.id = 'auth-styles';
        style.textContent = `
            .auth-page {
                display: flex;
                min-height: 100vh;
                overflow: hidden;
            }
            .auth-left {
                flex: 1;
                padding: 64px 56px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                background: var(--primary);
            }
            .auth-brand { display: flex; align-items: center; gap: 12px; }
            .auth-brand-icon {
                width: 44px; height: 44px;
                background: rgba(255,255,255,0.15);
                border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.25rem;
                border: 1px solid rgba(255,255,255,0.2);
            }
            .auth-brand-text .name {
                font-size: 1.2rem; font-weight: 700;
                display: block; color: #ffffff;
            }
            .auth-brand-text .sub {
                font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase;
                color: rgba(255,255,255,0.7);
            }
            .auth-hero-text { margin: 40px 0; }
            .auth-hero-text h2 {
                font-size: 2.2rem; font-weight: 700;
                line-height: 1.3; margin-bottom: 16px; color: #ffffff;
            }
            .auth-hero-text p {
                font-size: 0.95rem; color: rgba(255,255,255,0.8); line-height: 1.7; max-width: 360px;
            }
            .auth-features {
                display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
            }
            .auth-feat-pill {
                background: rgba(0,0,0,0.15);
                border: 1px solid rgba(255,255,255,0.15);
                border-radius: 8px; padding: 12px 14px;
                display: flex; align-items: center; gap: 10px;
                color: #ffffff; font-size: 0.85rem; font-weight: 500;
            }
            .auth-feat-pill .pill-icon, .auth-feat-pill i.pill-icon {
                font-size: 0.9rem; width: 16px; text-align: center; color: rgba(255,255,255,0.9);
            }
            .auth-left-footer { font-size: 0.75rem; color: rgba(255,255,255,0.6); }
            .auth-right {
                flex: 1; display: flex; flex-direction: column;
                justify-content: center; padding: 60px 64px;
                overflow-y: auto; background: #ffffff;
                border-left: 1px solid var(--border);
            }
            .role-tabs {
                display: flex;
                background: var(--bg-main);
                border: 1px solid var(--border);
                border-radius: 8px; padding: 4px;
                margin-bottom: 28px; gap: 4px;
            }
            .role-tab i { font-size: 0.8rem; margin-right: 4px; }
            .role-tab {
                flex: 1; padding: 9px 6px; border: none;
                background: transparent; border-radius: 6px;
                font-size: 0.82rem; font-weight: 500;
                color: var(--text-muted); cursor: pointer;
                transition: all 0.2s ease; text-align: center;
            }
            .role-tab.active {
                background: var(--primary); color: #ffffff; font-weight: 600;
            }
            .role-tab:hover:not(.active) {
                color: var(--primary); background: #E8F5E9;
            }
            .auth-form-header { margin-bottom: 24px; text-align: center; }
            .auth-form-header h1 {
                font-size: 1.6rem; font-weight: 700;
                color: var(--text-main); margin-bottom: 6px;
            }
            .auth-form-header p { color: var(--text-muted); font-size: 0.875rem; }
            .form-mode-toggle {
                display: flex;
                background: var(--bg-main);
                border: 1px solid var(--border);
                border-radius: 8px; padding: 4px;
                margin-bottom: 20px; gap: 4px;
            }
            .form-mode-btn {
                flex: 1; padding: 8px; border: none;
                background: transparent; border-radius: 6px;
                font-size: 0.875rem; font-weight: 500;
                color: var(--text-muted); cursor: pointer; transition: all 0.2s;
            }
            .form-mode-btn.active {
                background: white; color: var(--text-main); font-weight: 600;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            }
            .auth-label {
                display: block; font-size: 0.875rem; font-weight: 500;
                color: var(--text-main); margin-bottom: 6px;
            }
            .auth-input-wrap { position: relative; margin-bottom: 16px; }
            .auth-input-icon {
                position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
                color: var(--text-muted); font-size: 0.875rem; pointer-events: none;
                width: 16px; text-align: center;
            }
            .auth-input-toggle i { font-size: 0.875rem; }
            .auth-input-icon i { font-size: 0.875rem; }
            .auth-input {
                width: 100%; padding: 10px 12px 10px 38px;
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 6px; color: var(--text-main);
                font-size: 0.875rem;
                transition: border-color 0.2s;
                outline: none;
            }
            .auth-input:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(46,125,50,0.08);
            }
            .auth-input::placeholder { color: #9CA3AF; }
            .auth-input option { background: #ffffff; color: var(--text-main); }
            .auth-input-toggle {
                position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                background: none; border: none; cursor: pointer;
                color: var(--text-muted); padding: 0; transition: color 0.2s;
            }
            .auth-input-toggle:hover { color: var(--primary); }
            .auth-input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            .auth-remember-row {
                display: flex; justify-content: space-between; align-items: center;
                margin-bottom: 20px; font-size: 0.875rem;
            }
            .auth-remember-row label {
                display: flex; align-items: center; gap: 7px;
                color: var(--text-muted); cursor: pointer; margin: 0;
            }
            .auth-remember-row input[type=checkbox] {
                width: 14px; height: 14px; accent-color: var(--primary);
            }
            .auth-forgot {
                color: var(--primary); font-weight: 500; font-size: 0.875rem;
                text-decoration: none; cursor: pointer;
                background: none; border: none;
            }
            .auth-forgot:hover { text-decoration: underline; }
            .auth-submit {
                width: 100%; padding: 11px;
                background: var(--primary); color: #ffffff; border: none;
                border-radius: 6px; font-size: 0.9rem; font-weight: 600;
                cursor: pointer; transition: background 0.2s;
                margin-top: 4px;
            }
            .auth-submit:hover { background: var(--primary-dark); }
            .admin-badge {
                background: #E8F5E9;
                border: 1px solid #A5D6A7;
                border-left: 3px solid var(--primary);
                border-radius: 6px; padding: 12px 16px;
                display: flex; align-items: center; gap: 12px;
                margin-bottom: 20px;
                color: var(--text-muted); font-size: 0.82rem;
            }
            .admin-badge .admin-icon { font-size: 1.25rem; color: var(--primary); }
            .admin-badge strong { color: var(--text-main); font-size: 0.875rem; display: block; margin-bottom: 2px; }
            .auth-switch-text {
                text-align: center; margin-top: 20px;
                font-size: 0.875rem; color: var(--text-muted);
            }
            .auth-switch-text a {
                color: var(--primary); font-weight: 600; text-decoration: none; cursor: pointer;
            }
            .auth-switch-text a:hover { text-decoration: underline; }
            @media (max-width: 900px) {
                .auth-page { flex-direction: column; }
                .auth-left { padding: 40px 32px; min-height: auto; }
                .auth-right { padding: 32px 24px; }
                .auth-input-grid { grid-template-columns: 1fr; }
                .auth-hero-text h2 { font-size: 1.6rem; }
            }
        `;
        document.head.appendChild(style);
    },

    _renderAuthPage(defaultRole, defaultMode) {
        this._injectAuthStyles();
        this.nav.style.display = 'none';

        const roles = [
            { id: 'NGO', label: 'NGO', icon: '🏢', desc: 'Food rescue organizations', color: '#0d9488' },
            { id: 'DONOR', label: 'Donor', icon: '🍱', desc: 'Surplus food contributors', color: '#f59e0b' },
            { id: 'VOLUNTEER', label: 'Volunteer', icon: '🤝', desc: 'Rescue mission helpers', color: '#8b5cf6' },
            { id: 'ADMIN', label: 'Admin', icon: '🔐', desc: 'Platform administrators', color: '#ef4444', loginOnly: true }
        ];

        const states = {
            role: defaultRole || 'NGO',
            mode: defaultMode || 'login'
        };

        const renderForm = () => {
            const r = roles.find(x => x.id === states.role);
            const isAdmin = r.loginOnly;
            const isLogin = states.mode === 'login' || isAdmin;

            document.getElementById('auth-role-forms').innerHTML = `
                ${!isAdmin ? `
                <div class="form-mode-toggle">
                    <button class="form-mode-btn ${isLogin ? 'active' : ''}" onclick="App._authSetMode('login')">Login</button>
                    <button class="form-mode-btn ${!isLogin ? 'active' : ''}" onclick="App._authSetMode('register')">Register</button>
                </div>` : ''}

                <div class="auth-form-header">
                    <h1>${isLogin ? 'Welcome Back' : 'Join the Mission'}</h1>
                    <p>${isLogin ? `Please log in to your ${r.label} account` : `Create your ${r.label} account today`}</p>
                </div>

                ${isAdmin ? `
                <div class="admin-badge">
                    <span class="admin-icon"><i class="fa-solid fa-user-shield"></i></span>
                    <div><strong>Admin Access Only</strong>Platform administrators use this portal. Registration is handled internally.</div>
                </div>` : ''}

                <form id="auth-main-form">
                    ${!isLogin ? `
                    <div class="auth-input-grid">
                        <div>
                            <label class="auth-label">Full Name</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon"><i class="fa-solid fa-user"></i></span>
                                <input class="auth-input" type="text" name="name" required placeholder="e.g. Priya Raj" autocomplete="name">
                            </div>
                        </div>
                        <div>
                            <label class="auth-label">Phone</label>
                            <div class="auth-input-wrap">
                                <span class="auth-input-icon"><i class="fa-solid fa-phone"></i></span>
                                <input class="auth-input" type="tel" name="phone" placeholder="9876543210" autocomplete="tel">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="auth-label">City <span style="color:var(--status-error)">*</span></label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-city"></i></span>
                            <input class="auth-input" type="text" name="city" required placeholder="e.g. Chennai">
                        </div>
                    </div>
                    ${states.role === 'NGO' ? `
                    <div>
                        <label class="auth-label">NGO / Organization Name</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-building"></i></span>
                            <input class="auth-input" type="text" name="org_name" placeholder="e.g. Hope Foundation" style="padding-left:42px">
                        </div>
                    </div>` : ''}
                    ${states.role === 'VOLUNTEER' ? `
                    <div>
                        <label class="auth-label">NGO Code (ID) <span style="color:var(--status-error)">*</span></label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-link"></i></span>
                            <input class="auth-input" type="number" name="belongs_to_ngo_id" required placeholder="Enter NGO ID provided by your organization">
                        </div>
                    </div>` : ''}
                    ` : ''}


                    <div>
                        <label class="auth-label">Email Address</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input class="auth-input" type="email" name="email" required placeholder="name@example.com" autocomplete="email">
                        </div>
                    </div>

                    <div>
                        <label class="auth-label">Password</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" name="password" id="auth-pwd" required placeholder="••••••••" autocomplete="${isLogin ? 'current-password' : 'new-password'}">
                            <button type="button" class="auth-input-toggle" onclick="App._togglePwd('auth-pwd', 'pwd-toggle')" id="pwd-toggle"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>

                    ${!isLogin ? `
                    <div>
                        <label class="auth-label">Confirm Password</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" name="confirm_password" id="auth-cpwd" required placeholder="••••••••" autocomplete="new-password">
                            <button type="button" class="auth-input-toggle" onclick="App._togglePwd('auth-cpwd', 'cpwd-toggle')" id="cpwd-toggle"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>` : ''}

                    ${states.role === 'VOLUNTEER' && !isLogin ? `
                    <div>
                        <label class="auth-label">Availability</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <select class="auth-input" name="availability" style="appearance:none">
                                <option value="weekdays">Weekdays</option>
                                <option value="weekends">Weekends</option>
                                <option value="anytime">Anytime</option>
                            </select>
                        </div>
                    </div>` : ''}

                    <input type="hidden" name="role" value="${states.role}">

                    ${isLogin ? `
                    <div class="auth-remember-row">
                        <label><input type="checkbox" name="remember"> Remember me</label>
                        <button type="button" class="auth-forgot">Forgot Password?</button>
                    </div>` : ''}

                    <button type="submit" class="auth-submit">${isLogin ? '<i class="fa-solid fa-right-to-bracket"></i> Log In' : '<i class="fa-solid fa-user-plus"></i> Create Account'}</button>
                </form>

                <div class="auth-switch-text">
                    ${isLogin && !isAdmin
                    ? `Don't have an account? <a onclick="App._authSetMode('register')">Create one now</a>`
                    : (!isAdmin ? `Already have an account? <a onclick="App._authSetMode('login')">Log in here</a>` : '')
                }
                    ${!isLogin || (isLogin && isAdmin) ? '' : ''}
                    <br><small style="color:var(--text-muted)">— or —</small><br>
                    <a onclick="App.navigateTo('hero')" style="color:var(--text-muted); font-size:0.8rem">← Back to Home</a>
                </div>
            `;

            document.getElementById('auth-main-form').onsubmit = (e) => {
                e.preventDefault();
                const fd = new FormData(e.target);
                const data = Object.fromEntries(fd);
                if (!isLogin) {
                    if (data.password !== data.confirm_password) {
                        App.showToast('Security check failed: Passwords do not match!', 'error'); return;
                    }
                    delete data.confirm_password;
                    App.register(data);
                } else {
                    App.login(data);
                }
            };
        };

        this.app.innerHTML = `
            <div class="auth-page animate-fade">
                <!-- LEFT PANEL -->
                <div class="auth-left">
                    <div class="auth-brand">
                        <div class="auth-brand-icon"><i class="fa-solid fa-leaf" style="color:var(--primary)"></i></div>
                        <div class="auth-brand-text">
                            <span class="name">FOOD<span>RESCUE</span></span>
                            <span class="sub" style="letter-spacing:3px">Operational Control</span>
                        </div>
                    </div>

                    <div class="auth-hero-text">
                        <h2>Synchronizing<br>Surplus Resources</h2>
                        <p>A professional ecosystem connecting verified providers to logistical units. We optimize food redistribution with precision telemetry.</p>
                    </div>

                    <div class="auth-features">
                        <div class="auth-feat-pill"><i class="fa-solid fa-bolt pill-icon"></i> Instant Alerts</div>
                        <div class="auth-feat-pill"><i class="fa-solid fa-compass pill-icon"></i> Smart Logistics</div>
                        <div class="auth-feat-pill"><i class="fa-solid fa-network-wired pill-icon"></i> NGO Network</div>
                        <div class="auth-feat-pill"><i class="fa-solid fa-check-double pill-icon"></i> Verified Auth</div>
                    </div>

                    <div class="auth-left-footer">
                        © 2026 FOODRESCUE GLOBAL OPS. SECURE HANDSHAKE ENABLED.
                    </div>
                </div>

                <!-- RIGHT PANEL -->
                <div class="auth-right">
                    <div class="role-tabs" id="auth-role-tabs">
                        <button class="role-tab ${states.role === 'NGO' ? 'active' : ''}" onclick="App._authSetRole('NGO')"><i class="fa-solid fa-building-ngo"></i> NGO</button>
                        <button class="role-tab ${states.role === 'DONOR' ? 'active' : ''}" onclick="App._authSetRole('DONOR')"><i class="fa-solid fa-heart"></i> Donor</button>
                        <button class="role-tab ${states.role === 'VOLUNTEER' ? 'active' : ''}" onclick="App._authSetRole('VOLUNTEER')"><i class="fa-solid fa-hands-holding-heart"></i> Volunteer</button>
                        <button class="role-tab ${states.role === 'ADMIN' ? 'active' : ''}" onclick="App._authSetRole('ADMIN')"><i class="fa-solid fa-user-shield"></i> System Admin</button>
                    </div>
                    <div id="auth-role-forms"></div>
                </div>
            </div>
        `;

        this._authState = states;
        renderForm();
        this._authRenderForm = renderForm;
    },

    _authSetRole(role) {
        this._authState.role = role;
        if (role === 'ADMIN') this._authState.mode = 'login';
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        const tabs = document.querySelectorAll('.role-tab');
        const map = { NGO: 0, DONOR: 1, VOLUNTEER: 2, ADMIN: 3 };
        if (tabs[map[role]]) tabs[map[role]].classList.add('active');
        this._authRenderForm();
    },

    _authSetMode(mode) {
        this._authState.mode = mode;
        this._authRenderForm();
    },

    _togglePwd(fieldId, toggleId) {
        const input = document.getElementById(fieldId || 'auth-pwd');
        const btn = document.getElementById(toggleId || 'pwd-toggle');
        if (!input || !btn) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    },

    renderLogin() {
        this._renderAuthPage('NGO', 'login');
    },

    renderRegister() {
        this._renderAuthPage('NGO', 'register');
    },

    async renderDashboard() {
        const { user, currentTab } = this.state;
        if (!user) return this.navigateTo('login');

        const tabLabel = currentTab.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

        this.app.innerHTML = `
            <div class="dashboard-container">
                ${this.renderSidebar()}
                <main class="main-content">
                    <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; padding-bottom:24px; border-bottom:1px solid var(--border)">
                        <div>
                            <h2 style="font-size:1.5rem; font-weight:700; color:var(--text-main)">${tabLabel}</h2>
                            <p style="color:var(--text-muted); font-size:0.875rem; margin-top:4px">Food Rescue & Donation Management</p>
                        </div>
                        <div style="display:flex; gap:12px; align-items:center">
                            <div style="text-align:right">
                                <div style="font-weight:600; font-size:0.9rem; color:var(--text-main)">${user.name}</div>
                                <div style="font-size:0.75rem; color:var(--secondary); font-weight:500; text-transform:uppercase; letter-spacing:0.05em">${user.role}</div>
                            </div>
                            <button class="btn btn-primary" onclick="App.logout()">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout
                            </button>
                        </div>
                    </header>
                    <div id="dashboard-content" class="animate-fade">
                        <p>Loading view...</p>
                    </div>
                    ${this.renderFooter()}
                </main>
            </div>
        `;

        this.renderTabContent(currentTab);
    },

    renderFooter() {
        const { user } = this.state;
        const year = new Date().getFullYear();

        return `
            <footer class="dashboard-footer">
                <div class="footer-container">
                    <!-- Column 1: Brand & Mission -->
                    <div class="footer-brand">
                        <h4>FOOD<span style="color:var(--secondary)">RESCUE</span></h4>
                        <p>Synchronizing global surplus resources with surgical precision. We bridge the gap between abundance and scarcity through advanced logistics.</p>
                    </div>

                    <!-- Column 2: Dashboard Ops -->
                    <div class="footer-col" style="padding-left: 20px;">
                        <h5>Control Panel</h5>
                        <div class="footer-links">
                            <a class="footer-link" onclick="App.renderDashboard()"><i class="fa-solid fa-gauge-high"></i> Overview</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Sustainability Report 2026')"><i class="fa-solid fa-leaf"></i> Sustainability</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Global Impact Map')"><i class="fa-solid fa-earth-americas"></i> Impact Map</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Community Forums')"><i class="fa-solid fa-users-viewfinder"></i> Community</a>
                        </div>
                    </div>

                    <!-- Column 3: Resources -->
                    <div class="footer-col">
                        <h5>Resources</h5>
                        <div class="footer-links">
                            <a class="footer-link" onclick="App.loadFooterPage('Knowledge Base')"><i class="fa-solid fa-book"></i> API Docs</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Support & Help Center')"><i class="fa-solid fa-headset"></i> Help Center</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Privacy Policy')"><i class="fa-solid fa-shield-halved"></i> Privacy</a>
                            <a class="footer-link" onclick="App.loadFooterPage('Terms of Service')"><i class="fa-solid fa-gavel"></i> Terms</a>
                        </div>
                    </div>

                    <!-- Column 4: Intelligence & Connect -->
                    <div class="footer-newsletter">
                        <h5>Rescue Intelligence</h5>
                        <p style="font-size:0.8rem; color:rgba(255,255,255,0.6); line-height:1.4">Get weekly telemetry reports on food rescue efficiency.</p>
                        <div class="newsletter-box">
                            <input type="email" placeholder="ops@example.com" id="footer-newsletter-email">
                            <button class="newsletter-btn" onclick="App.subscribeNewsletter()">Join</button>
                        </div>
                        <div style="margin-top:16px; display:flex; gap:12px; font-size:0.75rem">
                            <span style="color:var(--secondary)"><i class="fa-solid fa-circle-check"></i> System Stable</span>
                            <span><i class="fa-solid fa-signal"></i> v1.2.4-stable</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="footer-bottom">
                    <div>© ${year} FoodRescue Global Ops. Protected by end-to-end encryption.</div>
                    <div class="social-links">
                        <a class="social-link"><i class="fa-brands fa-linkedin"></i></a>
                        <a class="social-link"><i class="fa-brands fa-twitter"></i></a>
                        <a class="social-link"><i class="fa-brands fa-instagram"></i></a>
                        <a class="social-link"><i class="fa-brands fa-github"></i></a>
                    </div>
                </div>
            </footer>
        `;
    },

    subscribeNewsletter() {
        const email = document.getElementById('footer-newsletter-email').value;
        if (!email) {
            this.showToast('Please enter a valid operations email.', 'error');
            return;
        }
        this.showToast('Subscribed to Rescue Intelligence weekly!', 'success');
        document.getElementById('footer-newsletter-email').value = '';
    },

    loadFooterPage(title) {
        const content = document.getElementById('dashboard-content');
        if (!content) return;

        content.innerHTML = `
            <div class="footer-page-container animate-fade">
                <!-- Sidebar Nav for Footer Pages -->
                <aside class="footer-page-sidebar">
                    <h5>Categories</h5>
                    <div class="footer-sidebar-links">
                        <a class="active">Overview</a>
                        <a>Mission Details</a>
                        <a>Compliance Docs</a>
                        <a>Operational Safety</a>
                        <a>Contact Support</a>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="footer-page-main">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:32px">
                        <div>
                            <h2 style="font-size:1.75rem; color:var(--text-main); margin-bottom:8px">${title}</h2>
                            <p style="color:var(--text-muted)">Resource ID: FR-SYS-${Math.floor(Math.random() * 90000) + 10000}</p>
                        </div>
                        <button class="btn btn-outline" onclick="App.renderDashboard()">
                            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                        </button>
                    </div>

                    <div class="glass-card" style="margin-bottom:32px; padding:32px">
                        <h3 style="margin-bottom:20px; color:var(--primary)">1. Executive Summary</h3>
                        <p style="line-height:1.7; color:var(--text-main)">
                            The <strong>${title}</strong> serve as the official operational framework for the FoodRescue ecosystem. 
                            This documentation is dynamically generated based on your verified role as a <strong>${this.state.user.role}</strong> and the current 
                            system timestamp.
                        </p>
                        <p style="margin-top:20px; line-height:1.7; color:var(--text-muted)">
                            As part of our commitment to transparency and efficiency, all logistical protocols mentioned here are 
                            vetted by the International Food Security Compliance board (IFSC).
                        </p>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px">
                        <div class="glass-card" style="padding:24px">
                            <h4 style="margin-bottom:12px"><i class="fa-solid fa-chart-pie" style="color:var(--secondary)"></i> System Performance</h4>
                            <div style="height:8px; background:#f3f4f6; border-radius:4px; margin:16px 0">
                                <div style="width:94%; height:100%; background:var(--primary); border-radius:4px"></div>
                            </div>
                            <p style="font-size:0.8rem; color:var(--text-muted)">94% Efficiency Score based on current rescue metrics.</p>
                        </div>
                        <div class="glass-card" style="padding:24px">
                            <h4 style="margin-bottom:12px"><i class="fa-solid fa-clock" style="color:var(--primary)"></i> Real-time Sync</h4>
                            <p style="font-size:1.25rem; font-weight:700; color:var(--text-main)">0.4ms Lag</p>
                            <p style="font-size:0.8rem; color:var(--text-muted)">Platform telemetry is current as of ${new Date().toLocaleTimeString()}.</p>
                        </div>
                    </div>

                    <div class="glass-card" style="padding:32px">
                        <h3 style="margin-bottom:20px">2. Operational Directives</h3>
                        <p style="line-height:1.7; color:var(--text-main)">
                            Users must adhere to the security protocols detailed in the 2026 Rescuers Handbook. 
                            Failure to comply with these guidelines may result in immediate suspension of platform access 
                            to maintain the integrity of the food safety chain.
                        </p>
                        <div style="margin-top:24px; padding:16px; background:rgba(255,255,255,0.05); border-radius:8px; border-left:4px solid var(--secondary)">
                            <p style="font-style:italic; font-size:0.9rem">"Sustainability is not a goal, it is the standard by which we operate."</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    renderSidebar() {
        const { user, currentTab } = this.state;
        let menu = [];

        if (user.role === 'ADMIN') {
            menu = [
                { id: 'admin_overview', label: 'Dashboard', icon: 'fa-chart-bar' },
                { id: 'admin_users', label: 'Manage Users', icon: 'fa-users' },
                { id: 'admin_alerts', label: 'Manage Donations', icon: 'fa-boxes-stacked' },
                { id: 'admin_claims', label: 'Claims', icon: 'fa-file-contract' },
                { id: 'admin_delivery', label: 'Delivery Monitor', icon: 'fa-satellite-dish' },
                { id: 'admin_reports', label: 'Reports', icon: 'fa-chart-line' }
            ];
        } else if (user.role === 'DONOR') {
            menu = [
                { id: 'donor_overview', label: 'Dashboard', icon: 'fa-house' },
                { id: 'donor_create', label: 'Create Donation', icon: 'fa-circle-plus' },
                { id: 'donor_list', label: 'My Donations', icon: 'fa-box-open' },
                { id: 'donor_history', label: 'Donation History', icon: 'fa-clock-rotate-left' },
                { id: 'donor_profile', label: 'Profile', icon: 'fa-user-circle' }
            ];
        } else if (user.role === 'NGO') {
            menu = [
                { id: 'ngo_overview', label: 'Dashboard', icon: 'fa-house' },
                { id: 'ngo_available', label: 'Available Donations', icon: 'fa-location-dot' },
                { id: 'ngo_accepted', label: 'Accepted Donations', icon: 'fa-hand-holding-heart' },
                { id: 'ngo_track', label: 'Delivery Tracking', icon: 'fa-map-location-dot' },
                { id: 'ngo_volunteers', label: 'Volunteers', icon: 'fa-users' },
                { id: 'ngo_profile', label: 'Profile', icon: 'fa-building' }
            ];
        } else if (user.role === 'VOLUNTEER') {
            menu = [
                { id: 'vol_overview', label: 'Dashboard', icon: 'fa-house' },
                { id: 'vol_assigned', label: 'Assigned Deliveries', icon: 'fa-truck-fast' },
                { id: 'vol_map', label: 'Delivery Map', icon: 'fa-map-location-dot' },
                { id: 'vol_history', label: 'Delivery History', icon: 'fa-clock-rotate-left' },
                { id: 'vol_profile', label: 'Profile', icon: 'fa-user-circle' }
            ];
        }

        return `
            <aside class="sidebar">
                <div data-link="dashboard" style="display:flex; align-items:center; gap:10px; margin-bottom:40px; cursor:pointer; padding:0 12px">
                    <div style="width:32px; height:32px; background:var(--primary); border-radius:6px; display:flex; align-items:center; justify-content:center">
                        <i class="fa-solid fa-leaf" style="color:#ffffff; font-size:0.9rem"></i>
                    </div>
                    <span style="font-weight:700; font-size:1.1rem; color:#ffffff; letter-spacing:-0.01em">Food Rescue</span>
                </div>

                <div style="padding:0 8px; margin-bottom:8px">
                    <p style="font-size:0.65rem; font-weight:600; color:#6B7280; text-transform:uppercase; letter-spacing:0.12em; padding:0 8px; margin-bottom:8px">Navigation</p>
                    <nav class="sidebar-nav">
                        ${menu.map(item => `
                            <div class="nav-item ${currentTab === item.id ? 'active' : ''}" onclick="App.switchTab('${item.id}')">
                                <i class="fa-solid ${item.icon}" style="width:16px; text-align:center; font-size:0.875rem"></i>
                                <span>${item.label}</span>
                            </div>
                        `).join('')}
                    </nav>
                </div>

                <div style="margin-top:auto; padding:16px 8px">
                    <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); border-radius:6px; padding:12px 16px">
                        <p style="font-size:0.7rem; color:#6B7280; margin-bottom:4px">Signed in as</p>
                        <p style="font-size:0.875rem; color:#ffffff; font-weight:600">${user.name}</p>
                        <p style="font-size:0.7rem; color:#4CAF50; font-weight:500; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px">${user.role}</p>
                    </div>
                </div>
            </aside>
        `;
    },

    async renderTabContent(tab) {
        const content = document.getElementById('dashboard-content');

        switch (tab) {
            case 'admin_overview':
                this.loadAdminStats(content);
                break;
            case 'admin_users':
                this.loadAdminUsers(content);
                break;
            case 'admin_alerts':
                this.loadAdminAlerts(content);
                break;
            case 'admin_claims':
                this.loadAdminClaims(content);
                break;
            case 'admin_reports':
                this.loadAdminReports(content);
                break;
            case 'admin_delivery':
                this.loadAdminDeliveryMonitor(content);
                break;
            case 'donor_overview':
                this.loadDonorDashboard(content);
                break;
            case 'donor_create':
                this.renderDonorCreateForm(content);
                break;
            case 'donor_list':
                this.loadDonorMyDonations(content);
                break;
            case 'donor_history':
                this.loadDonorHistory(content);
                break;
            case 'donor_profile':
                this.loadDonorProfile(content);
                break;
            case 'ngo_overview':
                this.loadNGODashboard(content);
                break;
            case 'ngo_available':
                this.loadNGOAvailable(content);
                break;
            case 'ngo_accepted':
                this.loadNGOAccepted(content);
                break;
            case 'ngo_track':
                this.loadNGODeliveryTracking(content);
                break;
            case 'ngo_volunteers':
                this.loadNGOVolunteers(content);
                break;
            case 'ngo_profile':
                this.loadNGOProfile(content);
                break;
            case 'vol_overview':
                this.loadVolDashboard(content);
                break;
            case 'vol_assigned':
                this.loadVolAssigned(content);
                break;
            case 'vol_map':
                this.loadVolMap(content);
                break;
            case 'vol_history':
                this.loadVolHistory(content);
                break;
            case 'vol_profile':
                this.loadVolProfile(content);
                break;
            default:
                content.innerHTML = `<div class="glass-card text-center" style="padding:100px">
                    <h3>Tab "${tab.replace('_', ' ')}" Under Development</h3>
                    <p style="color:var(--text-muted)">This feature will be available in the next update.</p>
                </div>`;
        }
    },

    // ══════════════════════════════════════════════════════════
    //  DONOR DASHBOARD — Page 1: Overview / Summary
    // ══════════════════════════════════════════════════════════
    async loadDonorDashboard(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading dashboard...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_donor_dashboard.php?donor_id=${this.state.user.user_id}`);
            if (!res.success) { container.innerHTML = '<p>Failed to load dashboard.</p>'; return; }
            const d = res.data;

            const statusBadge = s => {
                const map = {
                    AVAILABLE: 'badge-active', CLAIMED: 'badge-pending',
                    COMPLETED: 'badge-completed', EXPIRED: 'badge-expired'
                };
                return `<span class="badge ${map[s] || 'badge-active'}">${s}</span>`;
            };

            container.innerHTML = `
                <div class="animate-fade">
                    <!-- Summary Cards -->
                    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); margin-bottom:32px">
                        <div class="stat-card">
                            <span class="label"><i class="fa-solid fa-box-open" style="margin-right:6px;color:var(--primary)"></i>Total Donations</span>
                            <span class="value">${d.total_donations}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label"><i class="fa-solid fa-circle-dot" style="margin-right:6px;color:#1976D2"></i>Active Donations</span>
                            <span class="value" style="color:#1976D2">${d.active_donations}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label"><i class="fa-solid fa-circle-check" style="margin-right:6px;color:var(--status-success)"></i>Completed</span>
                            <span class="value" style="color:var(--status-success)">${d.completed_donations}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label"><i class="fa-solid fa-utensils" style="margin-right:6px;color:#F59E0B"></i>Meals Donated</span>
                            <span class="value" style="color:#F59E0B">${d.meals_donated}+</span>
                        </div>
                    </div>

                    <!-- Impact Banner -->
                    <div style="background:#E8F5E9; border:1px solid #A5D6A7; border-left:4px solid var(--primary); border-radius:6px; padding:20px 24px; margin-bottom:32px; display:flex; align-items:center; gap:16px">
                        <i class="fa-solid fa-heart-pulse" style="font-size:1.5rem; color:var(--primary)"></i>
                        <div>
                            <p style="font-weight:600; color:var(--primary); font-size:1rem">Your Impact</p>
                            <p style="color:#2E7D32; font-size:0.875rem; margin-top:2px">You have helped provide an estimated <strong>${d.meals_donated}+ meals</strong> to people in need through ${d.completed_donations} completed donations. Thank you!</p>
                        </div>
                    </div>

                    <!-- Recent Donations Table -->
                    <div class="glass-card table-card">
                        <div class="table-header">
                            <div>
                                <h3>Recent Donations</h3>
                                <p>Your latest 5 food donation entries</p>
                            </div>
                            <button class="btn btn-primary" onclick="App.switchTab('donor_create')">
                                <i class="fa-solid fa-plus"></i> New Donation
                            </button>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead><tr>
                                    <th>ID</th><th>Food Name</th><th>Quantity</th><th>Status</th><th>Date</th>
                                </tr></thead>
                                <tbody>
                                    ${d.recent_donations.length ? d.recent_donations.map(r => `
                                        <tr>
                                            <td style="color:var(--text-muted); font-size:0.8rem">#${r.ALERT_ID}</td>
                                            <td><strong>${r.FOOD_TYPE}</strong></td>
                                            <td>${r.QUANTITY}</td>
                                            <td>${statusBadge(r.STATUS)}</td>
                                            <td style="color:var(--text-muted)">${r.CREATED_AT}</td>
                                        </tr>
                                    `).join('') : `<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted)">No donations yet. <a style="color:var(--primary); cursor:pointer; font-weight:500" onclick="App.switchTab('donor_create')">Create your first donation</a></td></tr>`}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading dashboard.</p>'; }
    },

    // ══════════════════════════════════════════════════════════
    //  DONOR DASHBOARD — Page 2: Create Donation
    // ══════════════════════════════════════════════════════════
    renderDonorCreateForm(container) {
        container.innerHTML = `
            <div class="glass-card animate-fade" style="max-width:680px; margin:0 auto">
                <div style="margin-bottom:24px">
                    <h3 style="font-size:1.125rem; font-weight:600">Create New Donation</h3>
                    <p style="color:var(--text-muted); font-size:0.875rem; margin-top:4px">Fill in the details below to list your surplus food for rescue.</p>
                </div>
                <form id="donor-create-form">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                        <div class="form-group">
                            <label>Food Name <span style="color:var(--status-error)">*</span></label>
                            <input type="text" name="food_name" required placeholder="e.g. Vegetable Biryani">
                        </div>
                        <div class="form-group">
                            <label>Food Category <span style="color:var(--status-error)">*</span></label>
                            <select name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="VEG">Vegetarian</option>
                                <option value="NON_VEG">Non-Vegetarian</option>
                                <option value="PACKED">Packed / Sealed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity <span style="color:var(--status-error)">*</span></label>
                            <input type="text" name="quantity" required placeholder="e.g. 20 kg / 50 packets">
                        </div>
                        <div class="form-group">
                            <label>Preparation Time</label>
                            <input type="text" name="preparation_time" placeholder="e.g. Prepared at 12:00 PM today">
                        </div>
                        <div class="form-group">
                            <label>Expiry / Best Before <span style="color:var(--status-error)">*</span></label>
                            <input type="datetime-local" name="expiry_time" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Number <span style="color:var(--status-error)">*</span></label>
                            <input type="tel" name="contact_number" required placeholder="e.g. 9876543210">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pickup Address <span style="color:var(--status-error)">*</span></label>
                        <input type="text" name="pickup_address" required placeholder="e.g. 12, MG Road">
                    </div>
                    <div class="form-group">
                        <label>City <span style="color:var(--status-error)">*</span></label>
                        <input type="text" name="city" required placeholder="e.g. Chennai">
                    </div>

                    <div class="form-group">
                        <label>Special Instructions</label>
                        <textarea name="special_instructions" rows="3" placeholder="e.g. Please bring containers. Handle with care." style="resize:vertical"></textarea>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:8px">
                        <button type="submit" class="btn btn-primary" style="flex:2">
                            <i class="fa-solid fa-paper-plane"></i> Submit Donation
                        </button>
                        <button type="button" class="btn btn-outline" style="flex:1" onclick="App.switchTab('donor_list')">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        `;

        document.getElementById('donor-create-form').onsubmit = async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            data.donor_id = this.state.user.user_id;
            data.expiry_time = data.expiry_time.replace('T', ' ') + ':00';

            const btn = e.target.querySelector('[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';

            try {
                const res = await this.apiPost('/donor/create_donation.php', data);
                if (res.success) {
                    this.showToast('Donation submitted successfully!', 'success');
                    this.switchTab('donor_list');
                } else {
                    this.showToast(res.message || 'Submission failed.', 'error');
                }
            } catch (err) {
                console.error('Submission error:', err);
                this.showToast('Server connection failed. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Donation';
            }
        };
    },

    // ══════════════════════════════════════════════════════════
    //  DONOR DASHBOARD — Page 3: My Donations
    // ══════════════════════════════════════════════════════════
    async loadDonorMyDonations(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading donations...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_my_donations.php?donor_id=${this.state.user.user_id}`);
            if (!res.success) { container.innerHTML = '<p>Failed to load donations.</p>'; return; }
            const donations = res.data.donations;

            const statusBadge = s => {
                const map = {
                    AVAILABLE: 'badge-active', CLAIMED: 'badge-pending',
                    COMPLETED: 'badge-completed', EXPIRED: 'badge-expired'
                };
                return `<span class="badge ${map[s] || 'badge-active'}">${s}</span>`;
            };

            container.innerHTML = `
                <div class="glass-card table-card animate-fade">
                    <div class="table-header">
                        <div>
                            <h3>My Donations</h3>
                            <p>All food donations you have created</p>
                        </div>
                        <button class="btn btn-primary" onclick="App.switchTab('donor_create')">
                            <i class="fa-solid fa-plus"></i> New Donation
                        </button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr>
                                <th>Donation ID</th><th>Food Name</th><th>Quantity</th>
                                <th>Location</th><th>Status</th><th>Created Date</th><th style="text-align:right">Actions</th>
                            </tr></thead>
                            <tbody id="my-donations-tbody">
                                ${donations.length ? donations.map(d => `
                                    <tr>
                                        <td style="color:var(--text-muted); font-size:0.8rem">#${d.ALERT_ID}</td>
                                        <td><strong>${d.FOOD_TYPE}</strong><br><small style="color:var(--text-muted)">${d.CATEGORY || 'GENERAL'}</small></td>
                                        <td>${d.QUANTITY}</td>
                                        <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${d.PICKUP_ADDRESS}</td>
                                        <td>${statusBadge(d.STATUS)}</td>
                                        <td style="color:var(--text-muted)">${d.CREATED_AT}</td>
                                        <td style="text-align:right">
                                            ${d.STATUS === 'AVAILABLE' ? `
                                                <button class="btn btn-outline" style="font-size:0.75rem; padding:6px 12px; color:var(--status-error); border-color:rgba(211,47,47,0.3)"
                                                    onclick="App.cancelDonation(${d.ALERT_ID}, this)">
                                                    <i class="fa-solid fa-xmark"></i> Cancel
                                                </button>
                                            ` : `<span style="color:var(--text-muted); font-size:0.8rem">—</span>`}
                                        </td>
                                    </tr>
                                `).join('') : `<tr><td colspan="7" style="text-align:center; padding:48px; color:var(--text-muted)">No donations found. <a style="color:var(--primary); cursor:pointer; font-weight:500" onclick="App.switchTab('donor_create')">Create your first donation</a></td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading donations.</p>'; }
    },

    async cancelDonation(alertId, btn) {
        if (!confirm('Are you sure you want to cancel this donation?')) return;
        btn.disabled = true;
        const res = await this.apiPost('/donor/cancel_donation.php', {
            donor_id: this.state.user.user_id,
            alert_id: alertId
        });
        if (res.success) {
            this.showToast('Donation cancelled.', 'success');
            this.loadDonorMyDonations(document.getElementById('dashboard-content'));
        } else {
            btn.disabled = false;
            this.showToast(res.message || 'Failed to cancel.', 'error');
        }
    },

    // ══════════════════════════════════════════════════════════
    //  DONOR DASHBOARD — Page 4: Donation History
    // ══════════════════════════════════════════════════════════
    async loadDonorHistory(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading history...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_donation_history.php?donor_id=${this.state.user.user_id}`);
            if (!res.success) { container.innerHTML = '<p>Failed to load history.</p>'; return; }
            const history = res.data.history;

            const statusBadge = s => {
                const map = { COMPLETED: 'badge-completed', EXPIRED: 'badge-expired' };
                return `<span class="badge ${map[s] || 'badge-pending'}">${s}</span>`;
            };

            container.innerHTML = `
                <div class="glass-card table-card animate-fade">
                    <div class="table-header">
                        <div>
                            <h3>Donation History</h3>
                            <p>Completed and expired past donations</p>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr>
                                <th>Donation ID</th><th>Food Name</th><th>Quantity</th>
                                <th>NGO Assigned</th><th>Pickup Date</th><th>Status</th>
                            </tr></thead>
                            <tbody>
                                ${history.length ? history.map(h => `
                                    <tr>
                                        <td style="color:var(--text-muted); font-size:0.8rem">#${h.ALERT_ID}</td>
                                        <td><strong>${h.FOOD_TYPE}</strong></td>
                                        <td>${h.QUANTITY}</td>
                                        <td>${h.NGO_ASSIGNED}</td>
                                        <td style="color:var(--text-muted)">${h.PICKUP_DATE || '—'}</td>
                                        <td>${statusBadge(h.STATUS)}</td>
                                    </tr>
                                `).join('') : `<tr><td colspan="6" style="text-align:center; padding:48px; color:var(--text-muted)">No history found yet.</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading history.</p>'; }
    },

    // ══════════════════════════════════════════════════════════
    //  DONOR DASHBOARD — Page 5: Profile
    // ══════════════════════════════════════════════════════════
    async loadDonorProfile(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading profile...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_profile.php?user_id=${this.state.user.user_id}`);
            const u = res.success ? res.data : this.state.user;

            container.innerHTML = `
                <div class="glass-card animate-fade" style="max-width:600px; margin:0 auto">
                    <div style="margin-bottom:28px">
                        <div style="width:56px; height:56px; background:#E8F5E9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:16px">
                            <i class="fa-solid fa-user" style="font-size:1.5rem; color:var(--primary)"></i>
                        </div>
                        <h3 style="font-size:1.125rem; font-weight:600">${u.NAME || u.name}</h3>
                        <p style="color:var(--text-muted); font-size:0.875rem">${u.EMAIL || u.email} &nbsp;·&nbsp; <span style="color:var(--primary); font-weight:500; text-transform:uppercase; font-size:0.75rem">${u.ROLE || u.role}</span></p>
                    </div>

                    <form id="donor-profile-form">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="${u.NAME || u.name || ''}" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="${u.PHONE || u.phone || ''}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="${u.EMAIL || u.email || ''}" disabled style="background:#F9FAFB; color:var(--text-muted); cursor:not-allowed">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" value="${u.ADDRESS || u.address || ''}" placeholder="Your full address">
                        </div>
                        <div class="form-group">
                            <label>Organization Name <span style="color:var(--text-muted); font-size:0.75rem">(Optional)</span></label>
                            <input type="text" name="org_name" value="${u.ORG_NAME || u.org_name || ''}" placeholder="Company or restaurant name">
                        </div>

                        <div style="border-top:1px solid var(--border); padding-top:20px; margin-top:8px; display:flex; gap:12px">
                            <button type="submit" class="btn btn-primary" style="flex:1">
                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-outline" style="flex:1"
                                onclick="App._showChangePwdModal()">
                                <i class="fa-solid fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            `;

            document.getElementById('donor-profile-form').onsubmit = async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target));
                data.user_id = this.state.user.user_id;

                const btn = e.target.querySelector('[type=submit]');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

                const res = await this.apiPost('/donor/update_profile.php', data);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';

                if (res.success) {
                    // Update cached user name
                    this.state.user.name = data.name;
                    localStorage.setItem('fr_user', JSON.stringify(this.state.user));
                    this.showToast('Profile updated successfully!', 'success');
                } else {
                    this.showToast(res.message || 'Update failed.', 'error');
                }
            };
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading profile.</p>'; }
    },

    _showChangePwdModal() {
        const existing = document.getElementById('pwd-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'pwd-modal';
        modal.style.cssText = 'position:fixed;inset:0;z-index:3000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4)';
        modal.innerHTML = `
            <div class="glass-card" style="width:100%;max-width:420px;margin:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
                    <h3 style="font-size:1rem;font-weight:600">Change Password</h3>
                    <button onclick="document.getElementById('pwd-modal').remove()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--text-muted)">&times;</button>
                </div>
                <form id="pwd-change-form">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required placeholder="Current password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required placeholder="Min 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required placeholder="Repeat new password">
                    </div>
                    <div style="display:flex;gap:12px;margin-top:8px">
                        <button type="submit" class="btn btn-primary" style="flex:1">Update Password</button>
                        <button type="button" class="btn btn-outline" style="flex:1" onclick="document.getElementById('pwd-modal').remove()">Cancel</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('pwd-change-form').onsubmit = async (e) => {
            e.preventDefault();
            const fd = Object.fromEntries(new FormData(e.target));
            if (fd.new_password !== fd.confirm_password) {
                this.showToast('Passwords do not match!', 'error'); return;
            }
            this.showToast('Password change not yet connected to backend.', 'error');
        };
    },

    async loadAdminStats(container) {
        container.innerHTML = '<p>Loading system intelligence...</p>';
        try {
            const res = await this.apiGet('/admin/get_stats.php');
            if (res.success) {
                const s = res.data;
                const tc = s.top_cards;
                container.innerHTML = `
                    <div class="stats-grid animate-fade">
                        <div class="glass-card stat-card">
                            <span class="label">User Network</span>
                            <span class="value gradient-text">${tc.total_users}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">${tc.total_donors} Donors / ${tc.total_ngos} NGOs</p>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Live Alerts</span>
                            <span class="value" style="color:#10b981">${tc.active_alerts}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Active Opportunities</p>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Rescues Today</span>
                            <span class="value" style="color:#f59e0b">${tc.completed_rescues}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Impact Delivered</p>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">System Efficiency</span>
                            <span class="value" style="color:#3b82f6">${tc.completion_rate}%</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Recovery Success Rate</p>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1.6fr 1fr; gap:32px; margin-bottom:32px;">
                        <div class="glass-card" style="padding:32px">
                            <div class="table-header" style="padding:0; margin-bottom:24px; border:none"><h3>Monthly Donation Trend</h3></div>
                            <canvas id="monthlyChart" height="120"></canvas>
                        </div>
                        <div class="glass-card" style="padding:32px">
                            <div class="table-header" style="padding:0; margin-bottom:24px; border:none"><h3>Role Composition</h3></div>
                            <canvas id="roleChart" height="220"></canvas>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:32px;">
                        <div class="glass-card table-card">
                            <div class="table-header"><h3>Recent Users</h3></div>
                            <div class="table-container" style="max-height: 280px; overflow-y: auto;">
                                <table>
                                    <tbody>
                                        ${s.recent_activity.users.map(u => `<tr><td>${u.NAME}</td><td style="text-align:right"><span class="badge" style="background:rgba(255,255,255,0.03); color:var(--text-muted)">${u.ROLE}</span></td></tr>`).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="glass-card table-card">
                            <div class="table-header"><h3>Latest Alerts</h3></div>
                            <div class="table-container" style="max-height: 280px; overflow-y: auto;">
                                <table>
                                    <tbody>
                                        ${s.recent_activity.alerts.map(a => `<tr><td><strong>${a.FOOD_TYPE}</strong><br><small style="color:var(--text-dim)">${a.QUANTITY}</small></td><td style="text-align:right"><span class="badge badge-${a.STATUS.toLowerCase()}">${a.STATUS}</span></td></tr>`).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="glass-card table-card">
                            <div class="table-header"><h3>Success Stories</h3></div>
                            <div class="table-container" style="max-height: 280px; overflow-y: auto;">
                                <table>
                                    <tbody>
                                        ${s.recent_activity.rescues.length ? s.recent_activity.rescues.map(r => `
                                            <tr>
                                                <td style="padding:16px 24px">
                                                    <strong>${r.FOOD_TYPE}</strong><br>
                                                    <small style="color:var(--text-dim)">${r.DONOR_NAME} &rarr; ${r.VOLUNTEER_NAME}</small>
                                                </td>
                                                <td style="text-align:right">
                                                    <span class="badge badge-completed">Done</span>
                                                </td>
                                            </tr>
                                        `).join('') : '<tr><td colspan="2" style="padding:40px; text-align:center; color:var(--text-dim)">Awaiting first rescue...</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                // Render Charts if Chart.js is loaded
                if (window.Chart) {
                    Chart.defaults.color = '#6B7280';
                    Chart.defaults.font.family = "'Inter', sans-serif";
                    Chart.defaults.font.size = 12;

                    const barColors = ['#F59E0B', '#1976D2', '#2E7D32'];
                    const monthLabels = s.charts.monthly_donations.labels || [];
                    const monthData = s.charts.monthly_donations.data || [];
                    const monthColors = monthData.map((_, i) => barColors[i % barColors.length]);

                    new Chart(document.getElementById('monthlyChart'), {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [{
                                label: 'Donations',
                                data: monthData,
                                backgroundColor: monthColors,
                                borderRadius: 4,
                                barThickness: 36
                            }]
                        },
                        options: {
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { color: '#F3F4F6' }, border: { display: false } },
                                x: { grid: { display: false }, border: { display: false } }
                            }
                        }
                    });

                    new Chart(document.getElementById('roleChart'), {
                        type: 'doughnut',
                        data: {
                            labels: s.charts.role_distribution.labels || [],
                            datasets: [{
                                data: s.charts.role_distribution.data || [],
                                backgroundColor: ['#F59E0B', '#1976D2', '#2E7D32'],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
                    });
                }
            }
        } catch (e) {
            console.error(e);
            container.innerHTML = '<p>Error loading stats.</p>';
        }
    },



    async loadDonorOverview(container) {
        container.innerHTML = '<p>Calculating your positive impact...</p>';
        try {
            const res = await this.apiGet(`/alerts/get_donor_stats.php?donor_id=${this.state.user.user_id}`);
            if (res.success) {
                const s = res.data;
                container.innerHTML = `
                    <div class="stats-grid animate-fade">
                        <div class="glass-card stat-card">
                            <span class="label">Total Contributions</span>
                            <span class="value gradient-text">${s.total_donations}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Items Donated</p>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Rescued & Completed</span>
                            <span class="value" style="color:#10b981">${s.completed_rescues}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Successful Deliveries</p>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Lives Impacted (Est.)</span>
                            <span class="value" style="color:#f59e0b">${s.completed_rescues * 10}</span>
                            <p style="color:var(--text-dim); font-size:0.75rem; margin-top:12px; font-weight:600">Community Support</p>
                        </div>
                    </div>
                    <div style="margin-top:48px">
                        <h3 style="margin-bottom:24px; font-size:1.4rem; font-weight:800; letter-spacing:-0.02em">Active Rescue Alerts</h3>
                        <div id="active-alerts-list">Loading...</div>
                    </div>
                `;
                this.loadDonorAlerts(document.getElementById('active-alerts-list'));
            }
        } catch (e) { container.innerHTML = '<p>Error loading donor stats.</p>'; }
    },
    async loadAdminUsers(container) {
        container.innerHTML = '<p>Fetching user directory...</p>';
        try {
            const res = await this.apiGet('/admin/manage_users.php');
            if (res.success) {
                // We'll store this globally for filtering
                window._allUsers = res.data.users;
                this._renderAdminUsersTable(container, window._allUsers);
            }
        } catch (e) { container.innerHTML = '<p>Error loading users.</p>'; }
    },

    _renderAdminUsersTable(container, users) {
        container.innerHTML = `
            <div class="glass-card table-card animate-fade" style="margin-bottom:32px;">
                <div class="table-header">
                    <h3>Authorized Personnel Directory</h3>
                    <p style="color:var(--text-dim); font-size:0.85rem; font-weight:500">Manage system access and roles</p>
                </div>
                <!-- Filters -->
                <div class="filter-row" style="background:rgba(255,255,255,0.01); border-bottom:1px solid var(--glass-border); padding:24px 32px">
                    <input type="text" id="user-search" style="flex:1;" placeholder="Search by name, email or ID..." oninput="App._filterUsers()">
                    <select id="user-role-filter" style="width:200px;" onchange="App._filterUsers()">
                        <option value="">All Project Roles</option>
                        <option value="DONOR">Donor (Providers)</option>
                        <option value="NGO">NGO (Receivers)</option>
                        <option value="VOLUNTEER">Volunteer (Logistic)</option>
                    </select>
                    <select id="user-status-filter" style="width:180px;" onchange="App._filterUsers()">
                        <option value="">Status: All</option>
                        <option value="ACTIVE">Active Only</option>
                        <option value="SUSPENDED">Suspended Only</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Identity</th>
                                <th>Contact Information</th>
                                <th>System Role</th>
                                <th>Registration</th>
                                <th>Current Status</th>
                                <th style="text-align:right">Management</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            ${this._genAdminUsersRows(users)}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    _genAdminUsersRows(users) {
        return users.map(u => `
            <tr>
                <td><strong>${u.NAME}</strong></td>
                <td style="color:var(--text-muted)">${u.EMAIL}</td>
                <td><span class="badge" style="background:rgba(255,255,255,0.03); color:var(--text-dim)">${u.ROLE}</span></td>
                <td>${u.JOINED}</td>
                <td><span class="badge badge-${u.STATUS.toLowerCase()}">${u.STATUS}</span></td>
                <td style="text-align:right">
                    <button class="btn btn-outline" style="padding:8px 14px; font-size:0.75rem; border-radius:10px" 
                        onclick="App.toggleUserStatus(${u.USER_ID}, '${u.STATUS === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE'}')">
                        ${u.STATUS === 'ACTIVE' ? '🔒 Suspend' : '🔓 Activate'}
                    </button>
                    <button class="btn" style="padding:8px 14px; font-size:0.75rem; background:#ef4444; color:white; margin-left:8px; border-radius:10px" onclick="App.deleteUser(${u.USER_ID})">🗑️ Delete</button>
                </td>
            </tr>
        `).join('');
    },

    _filterUsers() {
        if (!window._allUsers) return;
        const search = document.getElementById('user-search').value.toLowerCase();
        const role = document.getElementById('user-role-filter').value;
        const status = document.getElementById('user-status-filter').value;

        const filtered = window._allUsers.filter(u => {
            const matchesSearch = u.NAME.toLowerCase().includes(search) || u.EMAIL.toLowerCase().includes(search);
            const matchesRole = !role || u.ROLE === role;
            const matchesStatus = !status || u.STATUS === status;
            return matchesSearch && matchesRole && matchesStatus;
        });

        document.getElementById('user-table-body').innerHTML = this._genAdminUsersRows(filtered);
    },

    async toggleUserStatus(userId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus === 'ACTIVE' ? 'activate' : 'suspend'} this account?`)) return;
        try {
            // Note: The original POST endpoint uses the body {user_id, status} instead of {user_id, new_status}
            const res = await this.apiPost('/admin/manage_users.php', { user_id: userId, status: newStatus });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderTabContent('admin_users'); // reload
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showToast('Network error updating user', 'error');
        }
    },

    async loadAdminAlerts(container) {
        container.innerHTML = '<p>Fetching food alerts...</p>';
        try {
            const res = await this.apiGet('/admin/manage_alerts.php');
            if (res.success) {
                window._allAlerts = res.data.alerts;
                this._renderAdminAlertsTable(container, window._allAlerts);
            }
        } catch (e) { container.innerHTML = '<p>Error loading alerts.</p>'; }
    },

    _renderAdminAlertsTable(container, alerts) {
        container.innerHTML = `
            <div class="glass-card table-card animate-fade" style="margin-bottom:32px;">
                <div class="table-header">
                    <h3>Global Distribution Ledger</h3>
                    <p style="color:var(--text-dim); font-size:0.85rem; font-weight:500">Track and monitor all food rescue opportunities</p>
                </div>
                <!-- Filters -->
                <div class="filter-row" style="background:rgba(255,255,255,0.01); border-bottom:1px solid var(--glass-border); padding:24px 32px">
                    <select id="alert-status-filter" style="width:200px;" onchange="App._filterAlerts()">
                        <option value="">Filter by Lifecycle</option>
                        <option value="AVAILABLE">Available (Live)</option>
                        <option value="CLAIMED">Claimed (In Progress)</option>
                        <option value="COMPLETED">Completed (Rescued)</option>
                        <option value="EXPIRED">Expired (Missed)</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Rescue Item</th>
                                <th>Authorized Donor</th>
                                <th>Quantity</th>
                                <th>Broadcasting Since</th>
                                <th>Status</th>
                                <th style="text-align:right">Intervention</th>
                            </tr>
                        </thead>
                        <tbody id="alert-table-body">
                            ${this._genAdminAlertsRows(alerts)}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    _genAdminAlertsRows(alerts) {
        return alerts.map(a => `
            <tr>
                <td><strong>${a.FOOD_TYPE}</strong></td>
                <td>${a.DONOR_NAME}<br><small style="color:var(--text-muted); font-size:0.75rem">${a.DONOR_EMAIL}</small></td>
                <td><span style="font-weight:600; color:var(--text-white)">${a.QUANTITY}</span></td>
                <td style="color:var(--text-dim)">${a.CREATED_AT}</td>
                <td><span class="badge badge-${a.STATUS.toLowerCase()}">${a.STATUS}</span></td>
                <td style="text-align:right">
                    ${(a.STATUS === 'AVAILABLE' || a.STATUS === 'CLAIMED') ? `<button class="btn btn-outline" style="padding:8px 14px; font-size:0.75rem; color:#f59e0b; border-color:rgba(245,158,11,0.3); border-radius:10px" onclick="App.forceCloseAlert(${a.ALERT_ID})">🛑 Force Close</button>` : ''}
                    <button class="btn" style="padding:8px 14px; font-size:0.75rem; background:#ef4444; color:white; margin-left:8px; border-radius:10px" onclick="App.deleteAlert(${a.ALERT_ID})">🗑️ Delete</button>
                </td>
            </tr>
        `).join('');
    },

    _filterAlerts() {
        if (!window._allAlerts) return;
        const status = document.getElementById('alert-status-filter').value;
        const filtered = window._allAlerts.filter(a => !status || a.STATUS === status);
        document.getElementById('alert-table-body').innerHTML = this._genAdminAlertsRows(filtered);
    },

    async forceCloseAlert(alertId) {
        if (!confirm('Are you sure you want to forcefully mark this alert as expired?')) return;
        try {
            const res = await this.apiPost('/admin/manage_alerts.php', { alert_id: alertId, action: 'FORCE_CLOSE' });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderTabContent('admin_alerts');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (e) {
            this.showToast('Network error force closing alert', 'error');
        }
    },

    async deleteAlert(alertId) {
        if (!confirm('Are you sure you want to permanently delete this alert?')) return;
        try {
            const res = await this.apiPost('/admin/manage_alerts.php', { alert_id: alertId, action: 'DELETE' });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderTabContent('admin_alerts');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (e) {
            this.showToast('Network error deleting alert', 'error');
        }
    },

    async loadAdminClaims(container) {
        container.innerHTML = '<p>Fetching claims and transactions...</p>';
        try {
            const res = await this.apiGet('/admin/manage_claims.php');
            if (res.success) {
                container.innerHTML = `
                    <div class="glass-card table-card animate-fade" style="margin-bottom:32px;">
                        <div class="table-header">
                            <h3>Transaction Registry</h3>
                            <p style="color:var(--text-dim); font-size:0.85rem; font-weight:500">Live monitoring of donor-ngo logistics</p>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rescue Item</th>
                                        <th>Provider (Donor)</th>
                                        <th>Logistic Unit (NGO/Vol)</th>
                                        <th>Timestamp</th>
                                        <th>Delivery Status</th>
                                        <th style="text-align:right">Protocol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${res.data.claims.map(c => `
                                        <tr>
                                            <td><strong>${c.FOOD_TYPE}</strong></td>
                                            <td>${c.DONOR_NAME}</td>
                                            <td>${c.VOLUNTEER_NAME}</td>
                                            <td style="color:var(--text-dim)">${c.CLAIM_TIME}</td>
                                            <td><span class="badge badge-${c.CLAIM_STATUS.toLowerCase()}">${c.CLAIM_STATUS}</span></td>
                                            <td style="text-align:right">
                                                <div style="display:flex; gap:8px; justify-content:flex-end">
                                                    <button class="btn btn-outline" style="padding:8px 14px; font-size:0.75rem; color:var(--primary); border-radius:10px" onclick="App.renderTrackingView(${c.ALERT_ID})"><i class="fa-solid fa-satellite-dish"></i> Live</button>
                                                    ${c.CLAIM_STATUS === 'ACTIVE' ? `<button class="btn btn-outline" style="padding:8px 14px; font-size:0.75rem; color:#ef4444; border-color:rgba(239, 68, 68, 0.3); border-radius:10px" onclick="App.cancelClaim(${c.CLAIM_ID})">⚠️ Void</button>` : '<span style="color:var(--text-dim)">Archived</span>'}
                                                </div>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
        } catch (e) { container.innerHTML = '<p>Error loading claims.</p>'; }
    },

    async cancelClaim(claimId) {
        if (!confirm('Mark this claim as cancelled due to failed pickup?')) return;
        try {
            const res = await this.apiPost('/admin/manage_claims.php', { claim_id: claimId, action: 'FAILED_PICKUP' });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderTabContent('admin_claims');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (e) {
            this.showToast('Network error cancelling claim', 'error');
        }
    },

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This cannot be undone.')) return;
        try {
            const res = await this.apiPost('/admin/manage_users.php', { user_id: userId, action: 'DELETE' });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderTabContent('admin_users');
            } else {
                this.showToast(res.message, 'error');
            }
        } catch (e) {
            this.showToast('Network error deleting user', 'error');
        }
    },

    async loadNGOClaims(container) {
        container.innerHTML = '<p>Retrieving your rescue missions...</p>';
        try {
            const res = await this.apiGet(`/alerts/get_volunteer_claims.php?volunteer_id=${this.state.user.user_id}`);
            if (res.success) {
                if (res.data.claims.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:100px; color:var(--text-muted)">No active or past claims found. Start by claiming a nearby alert!</div>';
                    return;
                }

                container.innerHTML = `
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px">
                        ${res.data.claims.map(c => `
                            <div class="glass-card animate-fade">
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px">
                                    <span class="badge ${c.CLAIM_STATUS === 'ACTIVE' ? 'badge-available' : 'badge-claimed'}" style="padding:4px 12px; border-radius:6px; font-size:0.8rem">${c.CLAIM_STATUS}</span>
                                    <span style="color:var(--text-muted); font-size:0.8rem">Rescue ID: #${c.CLAIM_ID}</span>
                                </div>
                                <h3 style="margin-bottom:10px">${c.FOOD_TYPE}</h3>
                                <div style="font-size:0.9rem; color:var(--text-muted); margin-bottom:20px">
                                    <p>🛡 <strong>NGO/Volunteer Role</strong></p>
                                    <p>👤 Donor: ${c.DONOR_NAME} (${c.DONOR_PHONE})</p>
                                    <p>📦 Quantity: ${c.QUANTITY}</p>
                                    <p>⏰ Expires: ${c.EXPIRY_TIME}</p>
                                </div>
                                ${c.CLAIM_STATUS === 'ACTIVE' ? `
                                    <div style="display:flex; gap:10px; border-top:1px solid var(--glass-border); padding-top:15px">
                                        <button class="btn btn-primary" style="flex:2; justify-content:center" onclick="App.updateClaimStatus(${c.ALERT_ID}, 'COMPLETED')">Mark Collected</button>
                                        <button class="btn btn-outline" style="flex:1; justify-content:center; color:#ef4444; border-color:rgba(239, 68, 68, 0.2)" onclick="App.updateClaimStatus(${c.ALERT_ID}, 'CANCELLED')">Cancel</button>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        } catch (e) { container.innerHTML = '<p>Error loading claims.</p>'; }
    },

    async updateClaimStatus(alertId, status) {
        if (!confirm(`Are you sure you want to mark this rescue as ${status}?`)) return;

        const res = await this.apiPost('/alerts/update_status.php', {
            alert_id: alertId,
            volunteer_id: this.state.user.user_id,
            new_status: status
        });

        if (res.success) {
            this.showToast(`Rescue ${status.toLowerCase()} Successfully!`, 'success');
            this.renderDashboard();
        } else {
            this.showToast(res.message, 'error');
        }
    },

    async loadDonorAlerts(container) {
        container.innerHTML = '<p>Loading your alerts...</p>';
        try {
            const res = await this.apiGet(`/alerts/get_donor_alerts.php?donor_id=${this.state.user.user_id}`);
            if (res.success) {
                if (res.data.alerts.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:100px; color:var(--text-muted)">You haven\'t posted any surplus food yet.</div>';
                    return;
                }
                container.innerHTML = `
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px">
                        ${res.data.alerts.map(alert => `
                            <div class="glass-card">
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px">
                                    <span class="badge ${alert.status === 'AVAILABLE' ? 'badge-available' : 'badge-claimed'}" style="padding:4px 12px; border-radius:6px; font-size:0.8rem">${alert.status}</span>
                                    <span style="color:var(--text-muted); font-size:0.8rem">${alert.created_at}</span>
                                </div>
                                <h3 style="margin-bottom:5px">${alert.food_type}</h3>
                                <p style="font-size:0.9rem; color:var(--text-muted)">
                                    Quantity: ${alert.quantity}<br>
                                    Expires: ${alert.expiry_time}
                                </p>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        } catch (err) {
            container.innerHTML = '<p>Failed to load alerts.</p>';
        }
    },

    async loadNearbyAlerts(container) {
        container.innerHTML = '<p>Scanning for nearby food rescues...</p>';
        const lat = this.state.user.latitude || 12.9716;
        const lon = this.state.user.longitude || 77.5946;

        try {
            const res = await this.apiGet(`/alerts/get_nearby_alerts.php?latitude=${lat}&longitude=${lon}&radius=20`);
            if (res.success && res.data.alerts.length > 0) {
                container.innerHTML = `
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px">
                        ${res.data.alerts.map(alert => `
                            <div class="glass-card">
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px">
                                    <span class="badge badge-available" style="padding:4px 12px; border-radius:6px; font-size:0.8rem">${alert.status}</span>
                                    <span style="color:var(--text-muted); font-size:0.8rem">${alert.distance_km} km away</span>
                                </div>
                                <h3 style="margin-bottom:5px">${alert.food_type}</h3>
                                <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:15px">
                                    Quantity: ${alert.quantity}<br>
                                    Expires: ${alert.expiry_time}
                                </p>
                                <div style="border-top:1px solid var(--glass-border); padding-top:15px; margin-top:auto">
                                    <button class="btn btn-primary" style="width:100%; justify-content:center" onclick="App.claimAlert(${alert.alert_id})">Claim Now</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                container.innerHTML = '<div style="text-align:center; padding:100px; color:var(--text-muted)">No food rescues found nearby. Try again later!</div>';
            }
        } catch (err) {
            container.innerHTML = '<p>Failed to load rescues.</p>';
        }
    },

    async claimAlert(alertId) {
        if (!confirm('Are you sure you can pick up this food rescue?')) return;

        const res = await this.apiPost('/alerts/claim_alert.php', {
            alert_id: alertId,
            volunteer_id: this.state.user.user_id
        });

        if (res.success) {
            this.showToast('Rescued! Check your claims.', 'success');
            this.renderDashboard();
        } else {
            this.showToast(res.message, 'error');
        }
    },

    // ══════════════════════════════════════════════════════════
    //  NGO DASHBOARD — Page 1: Overview
    // ══════════════════════════════════════════════════════════
    async loadNGODashboard(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading NGO intelligence...</p>`;
        try {
            const res = await this.apiGet(`/ngo/get_ngo_dashboard.php?ngo_id=${this.state.user.user_id}`);
            if (!res.success) throw new Error();
            const { stats, recent } = res.data;

            container.innerHTML = `
                <div class="animate-fade">
                    <div class="stats-grid" style="margin-bottom:32px">
                        <div class="stat-card">
                            <span class="label">Available Now</span>
                            <span class="value" style="color:var(--primary)">${stats.available}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label">Your Active Rescues</span>
                            <span class="value" style="color:#1976D2">${stats.accepted}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label">Rescues Completed</span>
                            <span class="value" style="color:#2E7D32">${stats.completed}</span>
                        </div>
                        <div class="stat-card">
                            <span class="label">Volunteers</span>
                            <span class="value" style="color:#F59E0B">${stats.volunteers}</span>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="table-header"><h3>Recent Food Requests</h3></div>
                        <div class="table-container">
                            <table>
                                <thead><tr><th>Item</th><th>Location</th><th>Expiry</th><th>Action</th></tr></thead>
                                <tbody>
                                    ${recent.length ? recent.map(r => `
                                        <tr>
                                            <td><strong>${r.FOOD_TYPE}</strong><br><small>${r.QUANTITY}</small></td>
                                            <td>${r.PICKUP_ADDRESS}</td>
                                            <td style="color:#ef4444; font-weight:500">${r.EXPIRY}</td>
                                            <td><button class="btn btn-primary" onclick="App.acceptDonation(${r.ALERT_ID})">Accept</button></td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted)">No requests at the moment.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading dashboard.</p>'; }
    },

    // ══════════════════════════════════════════════════════════
    //  NGO DASHBOARD — Page 2: New Assigned Donations
    // ══════════════════════════════════════════════════════════
    async loadNGOAvailable(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Fetching assigned donations...</p>`;
        try {
            const res = await this.apiGet(`/ngo/get_available_donations.php?ngo_id=${this.state.user.user_id}`);
            const data = res.data.donations || [];

            container.innerHTML = `
                <div class="glass-card animate-fade">
                    <div class="table-header">
                        <h3>New Assigned Donations</h3>
                        <p style="color:var(--text-muted); font-size:0.85rem">Donations automatically routed to your NGO based on proximity.</p>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Item</th><th>Donor</th><th>City</th><th>Distance</th><th>Expires</th><th>Action</th></tr></thead>
                            <tbody>
                                ${data.length ? data.map(d => `
                                    <tr>
                                        <td>
                                            <strong>${d.FOOD_TYPE}</strong><br>
                                            <small style="color:var(--text-muted)">${d.CATEGORY || 'GENERAL'} - ${d.QUANTITY}</small>
                                        </td>
                                        <td>
                                            ${d.DONOR_NAME}<br>
                                            <small style="color:var(--primary)"><i class="fa-solid fa-phone"></i> ${d.CONTACT_NUMBER || 'N/A'}</small>
                                        </td>
                                        <td>${d.CITY || 'N/A'}</td>
                                        <td style="color:var(--primary); font-weight:600">${d.DISTANCE_KM} km</td>
                                        <td style="color:#ef4444; font-weight:500">${d.EXPIRY}</td>
                                        <td><button class="btn btn-primary btn-sm" onclick="App.acceptDonation(${d.ALERT_ID})">Accept Mission</button></td>
                                    </tr>
                                `).join('') : '<tr><td colspan="6" style="text-align:center; padding:48px; color:var(--text-muted)">No new donations assigned to your NGO in your city.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading assigned donations.</p>'; }
    },

    async acceptDonation(alertId) {
        if (!confirm('Accept this mission? Your NGO will be responsible for coordinate pickup.')) return;
        const res = await this.apiPost('/ngo/accept_donation.php', { ngo_id: this.state.user.user_id, alert_id: alertId });
        if (res.success) {
            this.showToast('Donation accepted! Status: CLAIMED', 'success');
            this.switchTab('ngo_accepted');
        } else {
            this.showToast(res.message, 'error');
        }
    },

    // ══════════════════════════════════════════════════════════
    //  NGO DASHBOARD — Page 3: Accepted Missions
    // ══════════════════════════════════════════════════════════
    async loadNGOAccepted(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Managing your accepted missions...</p>`;
        try {
            const res = await this.apiGet(`/ngo/get_accepted_donations.php?ngo_id=${this.state.user.user_id}`);
            const data = res.data.donations || [];

            container.innerHTML = `
                <div class="glass-card animate-fade">
                    <div class="table-header"><h3>Active & Past Missions</h3></div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Item</th><th>Donor</th><th>Pickup Location</th><th>Volunteer</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                ${data.length ? data.map(d => {
                const isAssigned = d.VOLUNTEER_NAME && d.VOLUNTEER_NAME !== 'Not Assigned';
                return `
                                    <tr>
                                        <td>
                                            <strong>${d.FOOD_TYPE}</strong><br>
                                            <small style="color:var(--text-muted)">${d.CATEGORY || 'GENERAL'} - ${d.QUANTITY}</small>
                                        </td>
                                        <td>
                                            ${d.DONOR_NAME}<br>
                                            <small style="color:var(--primary)"><i class="fa-solid fa-phone"></i> ${d.CONTACT_NUMBER || 'N/A'}</small>
                                        </td>
                                        <td>
                                            ${d.PICKUP_ADDRESS}<br>
                                            ${d.SPECIAL_INSTRUCTIONS ? `<small style="color:var(--status-error)"><i class="fa-solid fa-circle-exclamation"></i> ${d.SPECIAL_INSTRUCTIONS}</small>` : ''}
                                        </td>
                                        <td style="color:var(--primary); font-weight:500">${d.VOLUNTEER_NAME || 'Not Assigned'}</td>
                                        <td><span class="badge ${d.STATUS === 'COMPLETED' ? 'badge-completed' : 'badge-claimed'}">${d.STATUS}</span></td>
                                        <td>
                                            <div style="display:flex; gap:8px">
                                                ${!isAssigned && d.STATUS === 'CLAIMED' ? `<button class="btn btn-outline btn-sm" onclick="App.state.selectedAlert=${d.ALERT_ID}; App.loadVolunteersForAssignment()">Assign Vol</button>` : ''}
                                                ${isAssigned || d.STATUS === 'VOLUNTEER_ASSIGNED' ? `<button class="btn btn-outline btn-sm" style="color:var(--primary)" onclick="App.renderTrackingView(${d.ALERT_ID})"><i class="fa-solid fa-location-arrow"></i> Track</button>` : ''}
                                                ${d.STATUS !== 'COMPLETED' ? `<button class="btn btn-primary btn-sm" onclick="App.markNGOCompleted(${d.ALERT_ID})">Complete</button>` : '—'}
                                            </div>
                                        </td>
                                    </tr>
                                `;
            }).join('') : '<tr><td colspan="6" style="text-align:center; padding:48px; color:var(--text-muted)">No missions in progress.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading accepted donations.</p>'; }
    },

    async markNGOCompleted(alertId) {
        if (!confirm('Mark this rescue mission as COMPLETED?')) return;
        const res = await this.apiPost('/ngo/update_donation_status.php', { alert_id: alertId, status: 'COMPLETED' });
        if (res.success) {
            this.showToast('Mission marked as completed!', 'success');
            this.switchTab('ngo_accepted');
        }
    },

    async loadVolunteersForAssignment() {
        try {
            const res = await this.apiGet(`/ngo/get_volunteers.php?ngo_id=${this.state.user.user_id}&alert_id=${this.state.selectedAlert}`);
            if (res.success) {
                const vols = (res.data.volunteers || []).filter(v => v.STATUS === 'ACTIVE');
                if (!vols.length) { this.showToast('No active volunteers linked to your NGO.', 'warning'); return; }

                const choices = vols.map(v => `${v.USER_ID}: ${v.NAME} (${v.DISTANCE_KM || '?'} km away)`).join('\n');
                const volId = prompt(`Select Nearest Volunteer ID to assign for mission #${this.state.selectedAlert}:\n\n` + choices);

                if (volId) {
                    const r = await this.apiPost('/ngo/assign_volunteer.php', {
                        alert_id: this.state.selectedAlert,
                        volunteer_id: volId,
                        ngo_id: this.state.user.user_id
                    });
                    if (r.success) {
                        this.showToast('Volunteer assigned! Status: VOL_ASSIGNED', 'success');
                        this.switchTab('ngo_accepted');
                    } else this.showToast(r.message, 'error');
                }
            }
        } catch (e) { this.showToast('Error loading volunteers', 'error'); }
    },

    // ══════════════════════════════════════════════════════════
    //  NGO DASHBOARD — Page 4: My Volunteers
    // ══════════════════════════════════════════════════════════
    async loadNGOVolunteers(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading your volunteer network...</p>`;
        try {
            const res = await this.apiGet(`/ngo/get_volunteers.php?ngo_id=${this.state.user.user_id}`);
            const data = res.data.volunteers || [];

            container.innerHTML = `
                <div class="glass-card animate-fade">
                    <div class="table-header">
                        <h3>Your Volunteer Force</h3>
                        <p style="color:var(--text-muted); font-size:0.85rem">Only volunteers using your NGO ID (${this.state.user.user_id}) appear here.</p>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Volunteer Name</th><th>Contact</th><th>Email</th><th>Status</th></tr></thead>
                            <tbody>
                                ${data.map(v => `
                                    <tr>
                                        <td><strong>${v.NAME}</strong></td>
                                        <td>${v.PHONE || '—'}</td>
                                        <td>${v.EMAIL}</td>
                                        <td><span class="badge ${v.STATUS === 'ACTIVE' ? 'badge-active' : 'badge-suspended'}">${v.STATUS}</span></td>
                                    </tr>
                                `).join('') || '<tr><td colspan="4" style="text-align:center; padding:32px">No volunteers linked to your NGO yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading volunteers.</p>'; }
    },


    // ══════════════════════════════════════════════════════════
    //  NGO DASHBOARD — Page 5: Profile
    // ══════════════════════════════════════════════════════════
    async loadNGOProfile(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading organization data...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_profile.php?user_id=${this.state.user.user_id}`);
            const u = res.success ? res.data : this.state.user;

            container.innerHTML = `
                <div class="glass-card animate-fade" style="max-width:600px; margin:0 auto">
                    <h3 style="margin-bottom:24px">NGO Organization Profile</h3>
                    <form id="ngo-profile-form">
                        <div class="form-group">
                            <label>Organization Name</label>
                            <input type="text" name="name" value="${u.NAME || u.name}" required>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                            <div class="form-group">
                                <label>Registration Number</label>
                                <input type="text" name="org_reg_num" value="${u.ORG_REG_NUM || ''}" placeholder="NGO-XXXX-XXXX">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="${u.PHONE || u.phone || ''}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" rows="3">${u.ADDRESS || u.address || ''}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:16px">Save Profile Changes</button>
                    </form>
                </div>
            `;

            document.getElementById('ngo-profile-form').onsubmit = async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target));
                data.user_id = this.state.user.user_id;
                const r = await this.apiPost('/donor/update_profile.php', data);
                if (r.success) {
                    this.showToast('Profile updated!', 'success');
                    this.state.user.name = data.name;
                    localStorage.setItem('fr_user', JSON.stringify(this.state.user));
                    this.render();
                } else this.showToast(r.message, 'error');
            };
        } catch (e) { container.innerHTML = '<p>Error loading profile.</p>'; }
    },

    // ══════════════════════════════════════════════════════════
    //  VOLUNTEER DASHBOARD — Page 1: Overview
    // ══════════════════════════════════════════════════════════
    async loadVolDashboard(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Loading operations summary...</p>`;
        try {
            const res = await this.apiGet(`/volunteer/get_vol_stats.php?volunteer_id=${this.state.user.user_id}`);
            if (!res.success) throw new Error();
            const stats = res.data;

            container.innerHTML = `
                <div class="animate-fade">
                    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 32px">
                        <div class="stat-card" style="border-left: 4px solid #3b82f6">
                            <span class="label">Assigned Deliveries</span>
                            <span class="value" style="color:#3b82f6">${stats.assigned}</span>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px">Missions pending action</p>
                        </div>
                        <div class="stat-card" style="border-left: 4px solid #10b981">
                            <span class="label">Completed Deliveries</span>
                            <span class="value" style="color:#10b981">${stats.completed}</span>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px">Lifetime successful rescues</p>
                        </div>
                        <div class="stat-card" style="border-left: 4px solid #f59e0b">
                            <span class="label">Pending Pickups</span>
                            <span class="value" style="color:#f59e0b">${stats.pending}</span>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px">Ready for collection</p>
                        </div>
                        <div class="stat-card" style="border-left: 4px solid var(--primary)">
                            <span class="label">Today's Tasks</span>
                            <span class="value" style="color:var(--primary)">${stats.today}</span>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px">Daily goal progress</p>
                        </div>
                    </div>

                    <div class="glass-card" style="padding:40px; text-align:center; background:linear-gradient(135deg, rgba(13,148,136,0.05) 0%, rgba(13,148,136,0.02) 100%)">
                        <div style="font-size:3rem; margin-bottom:16px">🤝</div>
                        <h2 style="font-size:1.5rem; font-weight:700; color:var(--text-main); margin-bottom:12px">Ready for your next mission?</h2>
                        <p style="color:var(--text-muted); max-width:500px; margin:0 auto 24px">Your contribution directly impacts food security in ${this.state.user.city || 'your city'}. Head to assigned deliveries to start a rescue.</p>
                        <button class="btn btn-primary" onclick="App.switchTab('vol_assigned')">View Assigned Tasks</button>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading volunteer stats.</p>'; }
    },

    async loadVolAssigned(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Fetching assigned missions...</p>`;
        try {
            const res = await this.apiGet(`/volunteer/get_assigned_deliveries.php?volunteer_id=${this.state.user.user_id}`);
            const data = res.data.deliveries || [];

            container.innerHTML = `
                <div class="glass-card animate-fade">
                    <div class="table-header">
                        <h3>Active Rescue Missions</h3>
                        <p style="color:var(--text-muted); font-size:0.85rem">Deliveries assigned to you by NGO partners.</p>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>ID</th><th>Donor</th><th>Food Item</th><th>Quantity</th><th>Pickup Address</th><th>Delivery To</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                ${data.map(d => `
                                    <tr>
                                        <td>#${d.CLAIM_ID}</td>
                                        <td><strong>${d.DONOR_NAME}</strong></td>
                                        <td>${d.FOOD_TYPE}</td>
                                        <td>${d.QUANTITY}</td>
                                        <td><small>${d.PICKUP_ADDRESS}</small></td>
                                        <td><strong>${d.NGO_NAME}</strong></td>
                                        <td><span class="badge ${this._getVolStatusClass(d.STATUS)}">${d.STATUS}</span></td>
                                        <td>
                                            <button class="btn btn-outline btn-sm" onclick="App._openVolMissionMap(${JSON.stringify(d).replace(/"/g, '&quot;')})">View</button>
                                        </td>
                                    </tr>
                                `).join('') || '<tr><td colspan="8" style="text-align:center; padding:48px; color:var(--text-muted)">No active assignments found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading assigned deliveries.</p>'; }
    },

    _getVolStatusClass(s) {
        const map = {
            'Assigned': 'badge-available',
            'Picked Up': 'badge-pending',
            'On Delivery': 'badge-pending',
            'Delivered': 'badge-completed',
            'VOLUNTEER_ASSIGNED': 'badge-available'
        };
        return map[s] || 'badge-pending';
    },

    _openVolMissionMap(mission) {
        this.state.selectedMission = mission;
        this.switchTab('vol_map');
    },

    // ══════════════════════════════════════════════════════════
    //  VOLUNTEER DASHBOARD — Page 3: Pickup Details
    // ══════════════════════════════════════════════════════════
    async loadVolMap(container) {
        const mission = this.state.selectedMission;
        if (!mission) {
            container.innerHTML = `
                <div style="text-align:center; padding:60px" class="animate-fade">
                    <div style="font-size:3rem; margin-bottom:16px">📍</div>
                    <h3>No Mission Selected</h3>
                    <p style="color:var(--text-muted); margin-bottom:24px">Please select a mission from the Assigned Deliveries page to view the map.</p>
                    <button class="btn btn-primary" onclick="App.switchTab('vol_assigned')">Go to Assignments</button>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="animate-fade">
                <div class="glass-card" style="padding:0; overflow:hidden; border-radius:16px; margin-bottom:24px">
                    <div id="vol-mission-map" style="height:450px; background:#f0f0f0; width:100%"></div>
                    <div style="padding:16px 24px; border-top:1px solid rgba(0,0,0,0.05); display:flex; justify-content:space-between; align-items:center; background:#fff">
                        <div>
                            <p style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px">Remote Telemetry</p>
                            <div style="display:flex; gap:20px">
                                <div><i class="fa-solid fa-route" style="color:var(--primary)"></i> <span id="map-dist">-- km</span></div>
                                <div><i class="fa-solid fa-clock" style="color:var(--primary)"></i> <span id="map-time">-- mins</span></div>
                            </div>
                        </div>
                        <div style="display:flex; gap:12px">
                            ${mission.STATUS === 'Assigned' || mission.STATUS === 'VOLUNTEER_ASSIGNED' ? `
                                <button class="btn btn-primary" onclick="App.updateVolMissionStatus('start_pickup')">Start Pickup</button>
                            ` : ''}
                            ${mission.STATUS === 'Picked Up' ? `
                                <button class="btn btn-primary" onclick="App.updateVolMissionStatus('start_delivery')">Start Delivery</button>
                            ` : ''}
                            ${mission.STATUS === 'On Delivery' ? `
                                <button class="btn btn-secondary" style="background:#2ecc71; color:#fff; border:none" onclick="App.updateVolMissionStatus('mark_delivered')">Mark Delivered</button>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px">
                    <div class="glass-card" style="padding:24px">
                        <h4 style="margin-bottom:16px"><i class="fa-solid fa-store" style="color:var(--primary)"></i> Donor Focus</h4>
                        <p><strong>${mission.DONOR_NAME}</strong></p>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px">${mission.PICKUP_ADDRESS}</p>
                        <p style="color:var(--primary); font-size:0.85rem; margin-top:8px"><i class="fa-solid fa-phone"></i> ${mission.CONTACT_NUMBER || 'N/A'}</p>
                    </div>
                    <div class="glass-card" style="padding:24px">
                        <h4 style="margin-bottom:16px"><i class="fa-solid fa-building-ngo" style="color:var(--primary)"></i> NGO Distribution Center</h4>
                        <p><strong>${mission.NGO_NAME}</strong></p>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px">Target Distribution Hub</p>
                    </div>
                </div>
            </div>
        `;

        setTimeout(() => this._initVolMissionMap(mission), 100);
    },

    _initVolMissionMap(m) {
        const donorPos = [parseFloat(m.DONOR_LAT || 13.0827), parseFloat(m.DONOR_LNG || 80.2707)];
        const ngoPos = [parseFloat(m.NGO_LAT || 13.0475), parseFloat(m.NGO_LNG || 80.2090)];

        const map = L.map('vol-mission-map').setView(donorPos, 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const donorIcon = L.divIcon({ html: '<div style="background:var(--primary); color:#fff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.2)"><i class="fa-solid fa-box"></i></div>', className: '', iconSize: [30, 30] });
        const ngoIcon = L.divIcon({ html: '<div style="background:#3b82f6; color:#fff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.2)"><i class="fa-solid fa-building"></i></div>', className: '', iconSize: [30, 30] });

        L.marker(donorPos, { icon: donorIcon }).addTo(map).bindPopup(`<b>Donor:</b> ${m.DONOR_NAME}`);
        L.marker(ngoPos, { icon: ngoIcon }).addTo(map).bindPopup(`<b>NGO Hub:</b> ${m.NGO_NAME}`);

        const control = L.Routing.control({
            waypoints: [L.latLng(donorPos), L.latLng(ngoPos)],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            lineOptions: { styles: [{ color: '#0d9488', weight: 6, opacity: 0.7 }] },
            createMarker: () => null
        }).addTo(map);

        control.on('routesfound', (e) => {
            const routes = e.routes;
            const summary = routes[0].summary;
            document.getElementById('map-dist').innerText = (summary.totalDistance / 1000).toFixed(1) + ' km';
            document.getElementById('map-time').innerText = Math.round(summary.totalTime / 60) + ' mins';
        });
    },

    async updateVolMissionStatus(action) {
        if (!this.state.selectedMission) return;
        const alertId = this.state.selectedMission.ALERT_ID;
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

        try {
            const res = await this.apiPost(`/volunteer/${action}.php`, {
                alert_id: alertId,
                volunteer_id: this.state.user.user_id
            });

            if (res.success) {
                this.showToast(res.message, 'success');
                const statusMap = { 'start_pickup': 'Picked Up', 'start_delivery': 'On Delivery', 'mark_delivered': 'Delivered' };
                this.state.selectedMission.STATUS = statusMap[action];
                if (action === 'mark_delivered') {
                    this.state.selectedMission = null;
                    this.switchTab('vol_history');
                } else {
                    this.loadVolMap(document.getElementById('dashboard-content'));
                }
            } else {
                this.showToast(res.message, 'error');
                btn.disabled = false;
                btn.innerText = action.replace(/_/g, ' ').toUpperCase();
            }
        } catch (e) {
            this.showToast('Protocol failure: Database link disrupted.', 'error');
            btn.disabled = false;
        }
    },

    async loadVolHistory(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Analyzing performance history...</p>`;
        try {
            const res = await this.apiGet(`/volunteer/get_delivery_history.php?volunteer_id=${this.state.user.user_id}`);
            const data = res.data.history || [];

            container.innerHTML = `
                <div class="glass-card animate-fade">
                    <div class="table-header"><h3>Logistical Success History</h3></div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>ID</th><th>Donor Name</th><th>NGO Partner</th><th>Food Type</th><th>Completion Date</th><th>Status</th></tr></thead>
                            <tbody>
                                ${data.map(h => `
                                    <tr>
                                        <td>#${h.CLAIM_ID}</td>
                                        <td>${h.DONOR_NAME}</td>
                                        <td>${h.NGO_NAME}</td>
                                        <td><strong>${h.FOOD_TYPE}</strong></td>
                                        <td>${h.DELIVERY_DATE}</td>
                                        <td><span class="badge badge-completed">${h.STATUS}</span></td>
                                    </tr>
                                `).join('') || '<tr><td colspan="6" style="text-align:center; padding:48px; color:var(--text-muted)">No completed missions on record.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (e) { container.innerHTML = '<p>Error loading history.</p>'; }
    },

    async loadVolProfile(container) {
        container.innerHTML = `<p style="color:var(--text-muted); padding:24px">Retrieving profile telemetry...</p>`;
        try {
            const res = await this.apiGet(`/donor/get_profile.php?user_id=${this.state.user.user_id}`);
            const u = res.success ? res.data : this.state.user;

            container.innerHTML = `
                <div class="glass-card animate-fade" style="max-width:650px; margin:0 auto">
                    <div style="display:flex; align-items:center; gap:24px; margin-bottom:32px; padding-bottom:24px; border-bottom:1px solid rgba(0,0,0,0.05)">
                        <div style="width:80px; height:80px; background:var(--primary); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:2rem">
                            <i class="fa-solid fa-id-card-clip"></i>
                        </div>
                        <div>
                            <h2 style="font-size:1.5rem; margin-bottom:4px">${u.NAME || u.name}</h2>
                            <p style="color:var(--text-muted)">Certified Volunteer Hub ID: #${u.USER_ID}</p>
                        </div>
                    </div>

                    <form id="vol-profile-form">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="${u.NAME || u.name}" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="${u.PHONE || ''}" placeholder="Contact no">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="${u.EMAIL}" disabled style="background:#f9fafb">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px">
                            <div class="form-group">
                                <label>Assigned City</label>
                                <input type="text" name="city" value="${u.CITY || ''}" placeholder="Operational city">
                            </div>
                            <div class="form-group">
                                <label>Vehicle Type</label>
                                <input type="text" name="vehicle_type" value="${u.VEHICLE_TYPE || ''}" placeholder="e.g. Scooter, Car, Van">
                            </div>
                        </div>
                        
                        <div style="display:flex; gap:16px; margin-top:32px">
                            <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center">Update Profile Telemetry</button>
                            <button type="button" class="btn btn-outline" style="flex:1; justify-content:center" onclick="App._showChangePwdModal()">Change Security Key</button>
                        </div>
                    </form>
                </div>
            `;

            document.getElementById('vol-profile-form').onsubmit = async (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(e.target));
                data.user_id = this.state.user.user_id;
                const r = await this.apiPost('/donor/update_profile.php', data);
                if (r.success) {
                    this.showToast('Telemetry updated!', 'success');
                    this.state.user.name = data.name;
                    localStorage.setItem('fr_user', JSON.stringify(this.state.user));
                    this.render();
                } else this.showToast(r.message, 'error');
            };
        } catch (e) { container.innerHTML = '<p style="color:var(--status-error)">Error loading profile data.</p>'; }
    },

    async loadAdminReports(container) {
        container.innerHTML = '<p>Generating impact reports...</p>';
        try {
            const res = await this.apiGet('/admin/get_stats.php');
            if (res.success) {
                const s = res.data.top_cards;
                container.innerHTML = `
                    <div class="animate-fade" style="margin-bottom:32px">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px">
                            <div>
                                <h2 style="font-size:2rem; font-weight:800; letter-spacing:-0.03em">System Intelligence</h2>
                                <p style="color:var(--text-dim); font-size:0.95rem; font-weight:500; margin-top:4px">Real-time sustainability and performance indexing</p>
                            </div>
                            <div style="display:flex; gap:16px">
                                <button class="btn btn-outline" style="padding:12px 24px; font-size:0.9rem; border-radius:12px" onclick="App.exportCSV('impact')">📊 Export Raw Data</button>
                                <button class="btn btn-primary" style="padding:12px 24px; font-size:0.9rem; border-radius:12px" onclick="window.print()">📑 Generate PDF Report</button>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:32px; margin-bottom:48px">
                            <div class="stat-mini-card">
                                <span>CO2 Prevented</span>
                                <h3 class="gradient-text">${(s.completed_rescues * 2.5).toFixed(1)}kg</h3>
                                <p style="font-size:0.75rem; color:var(--text-dim); margin-top:12px; font-weight:600">Total Carbon Offset</p>
                            </div>
                            <div class="stat-mini-card">
                                <span>Meals Distributed</span>
                                <h3 style="color:#10b981">${s.completed_rescues * 8}+</h3>
                                <p style="font-size:0.75rem; color:var(--text-dim); margin-top:12px; font-weight:600">Nutritional Impact</p>
                            </div>
                            <div class="stat-mini-card">
                                <span>Efficiency Index</span>
                                <h3 style="color:#3b82f6">${s.completion_rate}%</h3>
                                <p style="font-size:0.75rem; color:var(--text-dim); margin-top:12px; font-weight:600">Logistic Reliability</p>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1.6fr 1fr; gap:32px">
                            <div class="glass-card" style="padding:32px">
                                <h4 style="margin-bottom:24px; font-size:1.2rem; font-weight:800; letter-spacing:-0.02em">Temporal Activity Matrix</h4>
                                <canvas id="reportMonthlyChart" height="240"></canvas>
                            </div>
                            <div class="glass-card" style="padding:32px">
                                <h4 style="margin-bottom:24px; font-size:1.2rem; font-weight:800; letter-spacing:-0.02em">Platform Wellness</h4>
                                <div id="topPerformersList" style="display:flex; flex-direction:column; gap:20px">
                                    <div style="padding:20px; background:rgba(255,255,255,0.02); border:1px solid var(--glass-border); border-radius:16px">
                                        <p style="color:var(--text-dim); font-size: 0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px">Community Scale</p>
                                        <div style="font-size:1.4rem; font-weight:800">${s.total_users} Active Contributors</div>
                                    </div>
                                    <div style="padding:24px; background:rgba(16, 185, 129, 0.05); border:1px solid rgba(16, 185, 129, 0.1); border-radius:16px; text-align:center">
                                        <div style="font-size:1.4rem; font-weight:900; color:var(--primary)">NETWORK OPTIMAL</div>
                                        <p style="color:var(--primary); font-size: 0.85rem; font-weight:600; margin-top:4px">System health is at peak performance</p>
                                    </div>
                                    <p style="color:var(--text-dim); font-size: 0.85rem; font-style:italic; font-weight:500">Live Telemetry: ${new Date().toLocaleTimeString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    const ctx = document.getElementById('reportMonthlyChart');
                    if (ctx) {
                        const rptColors = ['#F59E0B', '#1976D2', '#2E7D32'];
                        const rptData = res.data.charts.monthly_donations.data;
                        const rptBgColors = rptData.map((_, i) => rptColors[i % rptColors.length]);

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: res.data.charts.monthly_donations.labels,
                                datasets: [{
                                    label: 'Rescues',
                                    data: rptData,
                                    backgroundColor: rptBgColors,
                                    borderRadius: 4,
                                    barThickness: 44
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, grid: { color: '#F3F4F6' }, border: { display: false } },
                                    x: { grid: { display: false }, border: { display: false } }
                                }
                            }
                        });
                    }
                }, 100);
            }
        } catch (e) { container.innerHTML = '<p>Error loading reports.</p>'; }
    },

    exportCSV(type) {
        this.showToast(`Generating ${type} report...`, 'success');
        window.open(`${CONFIG.BASE_API}/admin/export.php?type=${type}`, '_blank');
    },

    renderCreateAlert() {
        const modal = document.createElement('div');
        modal.className = 'glass';
        modal.style = "position:fixed; inset:0; z-index:2000; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.8)";
        modal.innerHTML = `
            <div class="glass-card animate-fade" style="width:100%; max-width:500px">
                <h2 style="margin-bottom:20px">Create Food Alert</h2>
                <form id="alert-form">
                    <input type="hidden" name="donor_id" value="${this.state.user.user_id}">
                    <div class="form-group">
                        <label>Food Item Description</label>
                        <input type="text" name="food_type" required placeholder="e.g. Mixed Veg Curry & Rice">
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="text" name="quantity" required placeholder="e.g. 15 Portions">
                    </div>
                    <div class="form-group">
                        <label>Expiry Time</label>
                        <input type="datetime-local" name="expiry_time" required>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="number" step="any" name="latitude" value="${this.state.user.latitude || 12.9716}">
                        </div>
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="number" step="any" name="longitude" value="${this.state.user.longitude || 77.5946}">
                        </div>
                    </div>
                    <div style="display:flex; gap:15px; margin-top:10px">
                        <button type="button" class="btn btn-outline" style="flex:1; justify-content:center" onclick="this.closest('.glass').remove()">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="flex:2; justify-content:center">Broadcast to NGOs</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('alert-form').onsubmit = async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            data.expiry_time = data.expiry_time.replace('T', ' ') + ':00';

            const res = await this.apiPost('/alerts/create_alert.php', data);
            if (res.success) {
                this.showToast('Alert broadcasted successfully!', 'success');
                modal.remove();
                this.renderDashboard();
            } else {
                this.showToast(res.message, 'error');
            }
        };
    },

    async renderTrackingView(alertId) {
        const content = document.getElementById('dashboard-content');
        if (!content) return;

        content.innerHTML = '<div class="text-center" style="padding:100px"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary)"></i><p style="margin-top:10px">Initializing tracking systems...</p></div>';

        const res = await this.apiGet(`/volunteer/get_delivery_details.php?alert_id=${alertId}`);
        if (!res.success) {
            this.showToast(res.message, 'error');
            return;
        }

        const data = res.data;
        const status = data.DELIVERY_STATUS || 'PENDING';

        // Harmonize status for UI
        let displayStatus = status;
        if (status === 'PICKED_UP') displayStatus = 'FOOD_PICKED_UP';
        if (status === 'ACCEPTED') displayStatus = 'VOLUNTEER_ASSIGNED';

        let actionsHtml = '';
        if (this.state.user.role === 'VOLUNTEER') {
            if (displayStatus === 'VOLUNTEER_ASSIGNED' || displayStatus === 'PENDING')
                actionsHtml = `<button class="btn btn-primary w-full" style="width:100%" onclick="App.updateTrackingStatus(${alertId}, 'PICKUP_STARTED')">Start Movement</button>`;
            else if (displayStatus === 'PICKUP_STARTED')
                actionsHtml = `<button class="btn btn-primary w-full" style="width:100%" onclick="App.updateTrackingStatus(${alertId}, 'FOOD_PICKED_UP')">Confirm Pickup</button>`;
            else if (displayStatus === 'FOOD_PICKED_UP')
                actionsHtml = `<button class="btn btn-primary w-full" style="width:100%" onclick="App.updateTrackingStatus(${alertId}, 'DELIVERING')">Start Delivery</button>`;
            else if (displayStatus === 'DELIVERING')
                actionsHtml = `<button class="btn btn-primary w-full" style="width:100%" onclick="App.updateTrackingStatus(${alertId}, 'DELIVERED')">Confirm Delivery</button>`;
        }

        const statusBadgeColor = {
            VOLUNTEER_ASSIGNED: 'var(--primary)',
            PICKUP_STARTED: '#3b82f6',
            FOOD_PICKED_UP: '#8b5cf6',
            DELIVERING: '#f59e0b',
            DELIVERED: 'var(--primary-dark)',
            COMPLETED: '#22c55e'
        };
        const badgeColor = statusBadgeColor[displayStatus] || 'var(--primary)';

        content.innerHTML = `
            <div class="tracking-wrapper animate-fade" style="padding:24px; display:grid; grid-template-columns: 380px 1fr; gap:24px; height:calc(100vh - 120px)">
                <div class="tracking-sidebar" style="display:flex; flex-direction:column; gap:20px; overflow-y:auto; padding-right:8px">
                    <div class="glass-card" style="padding:24px">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                            <h3 style="font-size:1.15rem; font-weight:700">Mission Logistics</h3>
                            <span style="padding:4px 12px; border-radius:30px; font-size:0.7rem; font-weight:800; text-transform:uppercase; background:${badgeColor}20; color:${badgeColor}; border:1px solid ${badgeColor}40">
                                ${displayStatus.replace(/_/g, ' ')}
                            </span>
                        </div>


                        <div style="display:flex; flex-direction:column; gap:16px">
                            <div style="display:flex; gap:12px">
                                <div style="width:36px; height:36px; border-radius:10px; background:var(--primary-light); display:flex; align-items:center; justify-content:center; color:white; flex-shrink:0">
                                    <i class="fa-solid fa-box"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; letter-spacing:0.04em">Rescue Item</div>
                                    <div style="font-weight:700; color:var(--text-main)">${data.FOOD_TYPE}</div>
                                    <div style="font-size:0.85rem; color:var(--text-muted)">${data.QUANTITY} • ${data.CATEGORY || 'General'}</div>
                                </div>
                            </div>

                            <div style="display:flex; gap:12px">
                                <div style="width:36px; height:36px; border-radius:10px; background:rgba(211,47,47,0.1); display:flex; align-items:center; justify-content:center; color:#d32f2f; flex-shrink:0">
                                    <i class="fa-solid fa-hotel"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; letter-spacing:0.04em">Authorized Donor</div>
                                    <div style="font-weight:600">${data.DONOR_NAME}</div>
                                    <div style="font-size:0.85rem; color:var(--primary); font-weight:500"><i class="fa-solid fa-phone" style="font-size:0.7rem"></i> ${data.CONTACT_NUMBER || 'No contact'}</div>
                                </div>
                            </div>

                            <div style="display:flex; gap:12px">
                                <div style="width:36px; height:36px; border-radius:10px; background:rgba(17,66,20,0.1); display:flex; align-items:center; justify-content:center; color:#114214; flex-shrink:0">
                                    <i class="fa-solid fa-building-ngo"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; letter-spacing:0.04em">Receiver NGO</div>
                                    <div style="font-weight:600">${data.NGO_NAME}</div>
                                </div>
                            </div>

                            ${data.PREPARATION_TIME ? `
                            <div style="padding:12px; background:rgba(245,158,11,0.08); border-radius:12px; border:1px solid rgba(245,158,11,0.15); display:flex; gap:10px; align-items:center">
                                <i class="fa-regular fa-clock" style="color:#F59E0B"></i>
                                <span style="font-size:0.85rem; color:#92400E; font-weight:600">Prepared: ${data.PREPARATION_TIME}</span>
                            </div>` : ''}

                            <div style="padding:16px; background:var(--bg-soft); border-radius:14px; font-size:0.85rem">
                                <div style="color:var(--text-muted); font-size:0.7rem; font-weight:700; text-transform:uppercase; margin-bottom:4px">Pickup Address</div>
                                <div style="font-weight:600; line-height:1.4"><i class="fa-solid fa-location-dot" style="color:var(--primary); margin-right:6px"></i> ${data.PICKUP_ADDRESS || 'Location info N/A'}</div>
                            </div>

                            ${data.SPECIAL_INSTRUCTIONS ? `
                            <div style="padding:12px; background:rgba(239,68,68,0.05); border-radius:12px; border-left:4px solid #ef4444">
                                <div style="font-size:0.7rem; color:#ef4444; font-weight:800; text-transform:uppercase; margin-bottom:4px">Critical Instructions</div>
                                <div style="font-size:0.85rem; color:#b91c1c; font-weight:500">${data.SPECIAL_INSTRUCTIONS}</div>
                            </div>` : ''}
                        </div>
                    </div>

                    <div class="glass-card" style="padding:24px">
                        <h4 style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:16px">Telemetry Overview</h4>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                            <div style="padding:16px; background:var(--bg-white); border:1px solid var(--border); border-radius:16px; text-align:center">
                                <div style="font-size:0.7rem; color:var(--text-muted); margin-bottom:4px; font-weight:600">DISTANCE</div>
                                <div id="track-dist" style="font-size:1.1rem; font-weight:800; color:var(--primary)">-- km</div>
                            </div>
                            <div style="padding:16px; background:var(--bg-white); border:1px solid var(--border); border-radius:16px; text-align:center">
                                <div style="font-size:0.7rem; color:var(--text-muted); margin-bottom:4px; font-weight:600">EST. TIME</div>
                                <div id="track-eta" style="font-size:1.1rem; font-weight:800; color:var(--text-main)">-- min</div>
                            </div>
                        </div>

                        <div style="margin-top:24px; display:flex; flex-direction:column; gap:12px">
                            ${actionsHtml}
                        </div>
                    </div>

                    <button class="btn btn-outline" style="width:100%; border-radius:16px; padding:12px" onclick="App.renderDashboard()">
                        <i class="fa-solid fa-arrow-left"></i> Close Tracking
                    </button>
                </div>

                <div class="glass-card" style="padding:0; overflow:hidden; border-radius:24px; position:relative; border:2px solid var(--glass-border)">
                    <div id="tracking-map" style="width:100%; height:100%"></div>
                </div>
            </div>
        `;


        // Initialize Map
        setTimeout(() => this.initTrackingMap(data), 300);
    },

    // ── Nominatim geocoding helper ───────────────────────────────────────────
    async _geocodeAddress(address) {
        if (!address || !address.trim()) return null;
        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(address)}`;
            const res = await fetch(url, { headers: { 'Accept-Language': 'en' } });
            const json = await res.json();
            if (json && json.length > 0) {
                return { lat: parseFloat(json[0].lat), lng: parseFloat(json[0].lon) };
            }
        } catch (e) { console.warn('Geocoding failed:', address, e); }
        return null;
    },

    // ── Checks if a lat/lng pair is valid (not 0,0 and not NaN) ─────────────
    _isValidCoord(lat, lng) {
        return !isNaN(lat) && !isNaN(lng) && (Math.abs(lat) > 0.01 || Math.abs(lng) > 0.01);
    },

    async initTrackingMap(data) {
        let donorLat = parseFloat(data.LATITUDE);
        let donorLng = parseFloat(data.LONGITUDE);
        let ngoLat = parseFloat(data.NGO_LAT);
        let ngoLng = parseFloat(data.NGO_LNG);
        const status = data.DELIVERY_STATUS;

        // ── If coordinates are missing / 0,0, geocode from address ──────────
        const CHENNAI_LAT = 13.0827, CHENNAI_LNG = 80.2707; // default fallback

        if (!this._isValidCoord(donorLat, donorLng)) {
            const distEl = document.getElementById('track-dist');
            if (distEl) distEl.innerText = 'Locating...';
            const geo = await this._geocodeAddress(data.PICKUP_ADDRESS);
            if (geo) { donorLat = geo.lat; donorLng = geo.lng; }
            else { donorLat = CHENNAI_LAT; donorLng = CHENNAI_LNG; }
        }

        if (!this._isValidCoord(ngoLat, ngoLng)) {
            const geo = await this._geocodeAddress(data.NGO_ADDRESS || data.NGO_NAME);
            if (geo) { ngoLat = geo.lat; ngoLng = geo.lng; }
            else { ngoLat = donorLat + 0.018; ngoLng = donorLng + 0.022; }
        }

        const donorPos = [donorLat, donorLng];
        const ngoPos = [ngoLat, ngoLng];
        const midLat = (donorLat + ngoLat) / 2;
        const midLng = (donorLng + ngoLng) / 2;

        const map = L.map('tracking-map', { zoomControl: false }).setView([midLat, midLng], 14);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // ── Custom Markers ───────────────────────────────────────────────────
        const mkIcon = (icon, color, label) => L.divIcon({
            html: `<div title="${label}" style="background:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:2.5px solid ${color}">
                       <i class="fa-solid ${icon}" style="color:${color};font-size:16px"></i>
                   </div>`,
            className: '',
            iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -22]
        });

        L.marker(donorPos, { icon: mkIcon('fa-location-dot', '#2E7D32', 'Donor') })
            .addTo(map)
            .bindPopup(`<b>📦 Donor Pickup</b><br>${data.DONOR_NAME}<br><small>${data.PICKUP_ADDRESS || ''}</small>`);

        L.marker(ngoPos, { icon: mkIcon('fa-building-ngo', '#114214', 'NGO') })
            .addTo(map)
            .bindPopup(`<b>🏠 NGO Delivery</b><br>${data.NGO_NAME}<br><small>${data.NGO_ADDRESS || ''}</small>`);

        if (status && !['PENDING', 'ACCEPTED', 'DELIVERED'].includes(status)) {
            const volLat = parseFloat(data.VOL_LAT);
            const volLng = parseFloat(data.VOL_LNG);
            if (this._isValidCoord(volLat, volLng)) {
                L.marker([volLat, volLng], { icon: mkIcon('fa-motorcycle', '#f59e0b', 'Volunteer') })
                    .addTo(map)
                    .bindPopup(`<b>🛵 Volunteer</b><br>${data.VOL_NAME || 'En Route'}`);
            }
        }

        // ── Real Road Routing via Leaflet Routing Machine + OSRM ────────────
        if (typeof L.Routing !== 'undefined') {
            const control = L.Routing.control({
                waypoints: [L.latLng(...donorPos), L.latLng(...ngoPos)],
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),
                routeWhileDragging: false,
                show: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                lineOptions: {
                    styles: [{ color: '#2E7D32', weight: 5, opacity: 0.85 }],
                    extendToWaypoints: true,
                    missingRouteTolerance: 0
                },
                createMarker: () => null
            }).addTo(map);

            control.on('routesfound', (e) => {
                const route = e.routes[0].summary;
                const distEl = document.getElementById('track-dist');
                const etaEl = document.getElementById('track-eta');
                if (distEl) distEl.innerText = (route.totalDistance / 1000).toFixed(1) + ' km';
                if (etaEl) etaEl.innerText = Math.ceil(route.totalTime / 60) + ' mins';
            });

            control.on('routingerror', () => {
                const dist = (map.distance(donorPos, ngoPos) / 1000).toFixed(1);
                const distEl = document.getElementById('track-dist');
                const etaEl = document.getElementById('track-eta');
                if (distEl) distEl.innerText = dist + ' km (est.)';
                if (etaEl) etaEl.innerText = Math.ceil(dist * 7) + ' mins (est.)';
                L.polyline([donorPos, ngoPos], { color: '#2E7D32', weight: 4, opacity: 0.5, dashArray: '8,12' }).addTo(map);
                map.fitBounds([donorPos, ngoPos], { padding: [60, 60] });
            });

        } else {
            // LRM library not loaded — draw fallback line
            const dist = (map.distance(donorPos, ngoPos) / 1000).toFixed(1);
            const distEl = document.getElementById('track-dist');
            const etaEl = document.getElementById('track-eta');
            if (distEl) distEl.innerText = dist + ' km (est.)';
            if (etaEl) etaEl.innerText = Math.ceil(dist * 7) + ' mins (est.)';
            L.polyline([donorPos, ngoPos], { color: '#2E7D32', weight: 4, opacity: 0.5, dashArray: '8,12' }).addTo(map);
            map.fitBounds([donorPos, ngoPos], { padding: [60, 60] });
        }
    },

    // ══════════════════════════════════════════════════════════════
    //  Shared helper – render a route map into any div element
    // ══════════════════════════════════════════════════════════════
    _renderRouteMap(mapDivId, donorPos, ngoPos, labels = {}) {
        const mid = [(donorPos[0] + ngoPos[0]) / 2, (donorPos[1] + ngoPos[1]) / 2];
        const m = L.map(mapDivId, { zoomControl: true, scrollWheelZoom: false }).setView(mid, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(m);

        const mkIcon = (icon, color) => L.divIcon({
            html: `<div style="background:#fff;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 10px rgba(0,0,0,0.2);border:2px solid ${color}">
                        <i class="fa-solid ${icon}" style="color:${color};font-size:14px"></i>
                   </div>`,
            className: '', iconSize: [36, 36], iconAnchor: [18, 18], popupAnchor: [0, -18]
        });

        L.marker(donorPos, { icon: mkIcon('fa-location-dot', '#2E7D32') })
            .addTo(m).bindPopup(`<b>📦 Pickup</b><br>${labels.donor || 'Donor'}`);
        L.marker(ngoPos, { icon: mkIcon('fa-building-ngo', '#114214') })
            .addTo(m).bindPopup(`<b>🏠 Delivery</b><br>${labels.ngo || 'NGO'}`);

        if (typeof L.Routing !== 'undefined') {
            L.Routing.control({
                waypoints: [L.latLng(...donorPos), L.latLng(...ngoPos)],
                routeWhileDragging: false,
                show: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                lineOptions: { styles: [{ color: '#2E7D32', weight: 5, opacity: 0.85 }], extendToWaypoints: true, missingRouteTolerance: 0 },
                createMarker: () => null
            }).addTo(m).on('routingerror', () => {
                L.polyline([donorPos, ngoPos], { color: '#2E7D32', weight: 4, opacity: 0.5, dashArray: '8,12' }).addTo(m);
                m.fitBounds([donorPos, ngoPos], { padding: [40, 40] });
            });
        } else {
            L.polyline([donorPos, ngoPos], { color: '#2E7D32', weight: 4, opacity: 0.5, dashArray: '8,12' }).addTo(m);
            m.fitBounds([donorPos, ngoPos], { padding: [40, 40] });
        }

        return m;
    },

    // ══════════════════════════════════════════════════════════════
    //  NGO Dashboard — Delivery Tracking Page
    // ══════════════════════════════════════════════════════════════
    async loadNGODeliveryTracking(container) {
        container.innerHTML = `<div style="padding:40px;text-align:center"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary)"></i><p style="margin-top:12px;color:var(--text-muted)">Fetching active deliveries...</p></div>`;

        const ngoId = this.state.user.user_id;
        const res = await this.apiGet(`/ngo/get_accepted_donations.php?ngo_id=${ngoId}`);
        const donations = (res.data && res.data.donations) ? res.data.donations : [];

        const active = donations.filter(d => d.STATUS !== 'COMPLETED');

        if (!active.length) {
            container.innerHTML = `
                <div class="glass-card animate-fade" style="text-align:center;padding:60px">
                    <i class="fa-solid fa-map-location-dot" style="font-size:3rem;color:var(--border);margin-bottom:20px"></i>
                    <h3 style="color:var(--text-muted);margin-bottom:8px">No Active Deliveries</h3>
                    <p style="color:var(--text-muted);font-size:0.9rem">Claimed donations that are in transit will appear here with live route maps.</p>
                </div>`;
            return;
        }

        container.innerHTML = `
            <div class="animate-fade">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
                    <div style="width:40px;height:40px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center">
                        <i class="fa-solid fa-satellite-dish" style="color:#fff"></i>
                    </div>
                    <div>
                        <h3 style="font-size:1.1rem;color:var(--text-main)">Live Delivery Tracking</h3>
                        <p style="font-size:0.8rem;color:var(--text-muted)">${active.length} active rescue mission${active.length > 1 ? 's' : ''} in progress</p>
                    </div>
                </div>
                <div id="ngo-track-cards" style="display:flex;flex-direction:column;gap:32px"></div>
            </div>`;

        const cardsEl = document.getElementById('ngo-track-cards');

        for (const d of active) {
            const alertId = d.ALERT_ID;
            // Fetch full delivery details to get coordinates
            const detRes = await this.apiGet(`/volunteer/get_delivery_details.php?alert_id=${alertId}`);
            if (!detRes.success) continue;
            const det = detRes.data;

            const donorLat = parseFloat(det.LATITUDE);
            const donorLng = parseFloat(det.LONGITUDE);
            const ngoLat = parseFloat(det.NGO_LAT) || (donorLat + 0.012);
            const ngoLng = parseFloat(det.NGO_LNG) || (donorLng + 0.015);

            const statusColors = {
                'Assigned': '#3B82F6',
                'Picked Up': '#8B5CF6',
                'On Delivery': '#F59E0B',
                'Delivered': '#22C55E'
            };
            const statusColor = statusColors[det.DELIVERY_STATUS] || '#6B7280';
            const mapId = `ngo-map-${alertId}`;

            const card = document.createElement('div');
            card.className = 'glass-card';
            card.style = 'overflow:hidden;padding:0';
            card.innerHTML = `
                <div style="padding:20px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                            <strong style="font-size:1rem;color:var(--text-main)">${det.FOOD_TYPE}</strong>
                            <span style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;background:${statusColor}20;color:${statusColor};border:1px solid ${statusColor}40">${(det.DELIVERY_STATUS || 'PENDING').replace(/_/g, ' ')}</span>
                        </div>
                        <div style="font-size:0.85rem;color:var(--text-muted);display:flex;gap:20px;flex-wrap:wrap">
                            <span><i class="fa-solid fa-box" style="color:var(--primary)"></i> ${det.QUANTITY}</span>
                            <span><i class="fa-solid fa-user" style="color:var(--primary)"></i> Volunteer: <strong>${det.VOL_NAME || 'Not Assigned'}</strong></span>
                            <span><i class="fa-solid fa-phone" style="color:var(--primary)"></i> ${det.CONTACT_NUMBER || 'N/A'}</span>
                        </div>
                    </div>
                    <div style="text-align:right;font-size:0.8rem;color:var(--text-muted)">
                        <div>Pickup: ${det.PICKUP_ADDRESS || 'N/A'}</div>
                        <div>Delivery: ${det.NGO_ADDRESS || det.NGO_NAME}</div>
                        <div style="margin-top:6px;font-size:0.75rem">
                            <span id="ngo-dist-${alertId}" style="color:var(--primary);font-weight:600">Calculating...</span>
                            &nbsp;·&nbsp;
                            <span id="ngo-eta-${alertId}" style="color:var(--text-main)">-- mins</span>
                        </div>
                    </div>
                </div>
                <div id="${mapId}" style="height:320px;width:100%"></div>`;

            cardsEl.appendChild(card);

            // Render map after a tick so DOM is ready
            setTimeout(() => {
                const m = this._renderRouteMap(mapId, [donorLat, donorLng], [ngoLat, ngoLng], { donor: det.DONOR_NAME, ngo: det.NGO_NAME });
                if (m && typeof L.Routing !== 'undefined') {
                    m.eachLayer(layer => {
                        if (layer._router) {
                            layer.on('routesfound', e => {
                                const s = e.routes[0].summary;
                                const el1 = document.getElementById(`ngo-dist-${alertId}`);
                                const el2 = document.getElementById(`ngo-eta-${alertId}`);
                                if (el1) el1.innerText = (s.totalDistance / 1000).toFixed(1) + ' km';
                                if (el2) el2.innerText = Math.ceil(s.totalTime / 60) + ' mins';
                            });
                        }
                    });
                }
            }, 100);
        }
    },

    // ══════════════════════════════════════════════════════════════
    //  Admin — Delivery Monitoring Dashboard
    // ══════════════════════════════════════════════════════════════
    async loadAdminDeliveryMonitor(container) {
        container.innerHTML = `<div style="padding:40px;text-align:center"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary)"></i><p style="margin-top:12px;color:var(--text-muted)">Loading delivery monitor...</p></div>`;

        const res = await this.apiGet('/admin/get_all_trackings.php');
        const deliveries = res.success && Array.isArray(res.data) ? res.data : [];

        if (!deliveries.length) {
            container.innerHTML = `
                <div class="glass-card animate-fade" style="text-align:center;padding:60px">
                    <i class="fa-solid fa-satellite-dish" style="font-size:3rem;color:var(--border);margin-bottom:20px"></i>
                    <h3 style="color:var(--text-muted);margin-bottom:8px">No Active Deliveries</h3>
                    <p style="color:var(--text-muted);font-size:0.9rem">When volunteers are on active rescue missions, you can monitor all routes here in real-time.</p>
                </div>`;
            return;
        }

        container.innerHTML = `
            <div class="animate-fade">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center">
                            <i class="fa-solid fa-satellite-dish" style="color:#fff"></i>
                        </div>
                        <div>
                            <h3 style="font-size:1.1rem;color:var(--text-main)">Delivery Fleet Monitor</h3>
                            <p style="font-size:0.8rem;color:var(--text-muted)">${deliveries.length} active mission${deliveries.length > 1 ? 's' : ''} across all zones</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;font-size:0.8rem">
                        <span style="padding:4px 12px;border-radius:20px;background:#DCFCE7;color:#2E7D32;font-weight:600"><i class="fa-solid fa-circle fa-xs"></i> Live</span>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="glass-card" style="padding:0;margin-bottom:32px;overflow:hidden">
                    <div class="table-header">
                        <h3>Active Deliveries Overview</h3>
                        <span style="font-size:0.8rem;color:var(--text-muted)">${deliveries.length} records</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead><tr>
                                <th>ID</th><th>Food Item</th><th>Donor</th><th>NGO</th><th>Volunteer</th><th>Status</th><th>Action</th>
                            </tr></thead>
                            <tbody>
                                ${deliveries.map(d => {
            const sc = {
                'Assigned': '#3B82F6',
                'Picked Up': '#8B5CF6',
                'On Delivery': '#F59E0B',
                'Delivered': '#22C55E',
                'VOLUNTEER_ASSIGNED': '#3B82F6',
                'PENDING': '#6B7280'
            };
            const c = sc[d.DELIVERY_STATUS] || '#6B7280';
            return `<tr>
                                        <td style="color:var(--text-muted);font-size:0.8rem">#${d.ALERT_ID}</td>
                                        <td><strong>${d.FOOD_TYPE}</strong><br><small style="color:var(--text-muted)">${d.QUANTITY}</small></td>
                                        <td>${d.DONOR_NAME}</td>
                                        <td>${d.NGO_NAME}</td>
                                        <td>${d.VOL_NAME || '<span style="color:var(--text-muted)">Unassigned</span>'}</td>
                                        <td><span style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;background:${c}20;color:${c};border:1px solid ${c}40">${(d.DELIVERY_STATUS || 'PENDING').replace(/_/g, ' ')}</span></td>
                                        <td><button class="btn btn-outline btn-sm" onclick="App.adminShowRouteCard(${d.ALERT_ID})"><i class="fa-solid fa-map"></i> View Route</button></td>
                                    </tr>`;
        }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Route Cards (rendered on demand) -->
                <div id="admin-route-cards" style="display:flex;flex-direction:column;gap:28px"></div>
            </div>`;
    },

    async adminShowRouteCard(alertId) {
        const cardsEl = document.getElementById('admin-route-cards');
        if (!cardsEl) return;

        // Avoid duplicate cards
        if (document.getElementById(`admin-map-${alertId}`)) {
            document.getElementById(`admin-map-${alertId}`).scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const res = await this.apiGet(`/volunteer/get_delivery_details.php?alert_id=${alertId}`);
        if (!res.success) { this.showToast('Could not load delivery details.', 'error'); return; }
        const det = res.data;

        const donorLat = parseFloat(det.LATITUDE);
        const donorLng = parseFloat(det.LONGITUDE);
        const ngoLat = parseFloat(det.NGO_LAT) || (donorLat + 0.012);
        const ngoLng = parseFloat(det.NGO_LNG) || (donorLng + 0.015);
        const mapId = `admin-map-${alertId}`;

        const statusColors = {
            'Assigned': '#3B82F6',
            'Picked Up': '#8B5CF6',
            'On Delivery': '#F59E0B',
            'Delivered': '#22C55E'
        };
        const sc = statusColors[det.DELIVERY_STATUS] || '#6B7280';

        const card = document.createElement('div');
        card.className = 'glass-card animate-fade';
        card.style = 'overflow:hidden;padding:0';
        card.innerHTML = `
            <div style="padding:16px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:12px">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
                        <span style="font-size:0.75rem;font-weight:600;color:var(--text-muted)">#${alertId}</span>
                        <strong style="color:var(--text-main)">${det.FOOD_TYPE}</strong>
                        <span style="padding:2px 8px;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;background:${sc}20;color:${sc};border:1px solid ${sc}40">${(det.DELIVERY_STATUS || 'PENDING').replace(/_/g, ' ')}</span>
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-muted);display:flex;gap:16px;flex-wrap:wrap">
                        <span><i class="fa-solid fa-user" style="color:var(--primary)"></i> Vol: <strong>${det.VOL_NAME || 'N/A'}</strong></span>
                        <span><i class="fa-solid fa-hotel" style="color:var(--primary)"></i> ${det.DONOR_NAME}</span>
                        <span><i class="fa-solid fa-building-ngo" style="color:var(--primary)"></i> ${det.NGO_NAME}</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="text-align:right;font-size:0.8rem">
                        <div style="color:var(--primary);font-weight:600" id="adm-dist-${alertId}">Routing...</div>
                        <div style="color:var(--text-muted)" id="adm-eta-${alertId}">--</div>
                    </div>
                    <button onclick="this.closest('.glass-card').remove()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.2rem">✕</button>
                </div>
            </div>
            <div id="${mapId}" style="height:360px;width:100%"></div>`;

        cardsEl.prepend(card);
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });

        setTimeout(() => {
            const m = this._renderRouteMap(mapId, [donorLat, donorLng], [ngoLat, ngoLng], { donor: det.DONOR_NAME, ngo: det.NGO_NAME });
            if (m) {
                m.eachLayer(layer => {
                    if (layer.on && layer._router) {
                        layer.on('routesfound', e => {
                            const s = e.routes[0].summary;
                            const el1 = document.getElementById(`adm-dist-${alertId}`);
                            const el2 = document.getElementById(`adm-eta-${alertId}`);
                            if (el1) el1.innerText = (s.totalDistance / 1000).toFixed(1) + ' km';
                            if (el2) el2.innerText = Math.ceil(s.totalTime / 60) + ' mins ETA';
                        });
                    }
                });
            }
        }, 150);
    },

    async updateTrackingStatus(alertId, newStatus) {
        let lat = null, lng = null;
        if ("geolocation" in navigator) {
            try {
                const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej));
                lat = pos.coords.latitude;
                lng = pos.coords.longitude;
            } catch (e) { console.log('Geolocation skipped', e); }
        }

        const res = await this.apiPost('/volunteer/update_tracking.php', {
            alert_id: alertId,
            status: newStatus,
            lat, lng
        });

        if (res.success) {
            this.showToast(`Status updated: ${newStatus.replace(/_/g, ' ')}`, 'success');
            this.renderTrackingView(alertId);
        } else {
            this.showToast(res.message, 'error');
        }
    },
};

window.App = App;
document.addEventListener('DOMContentLoaded', () => App.init());
