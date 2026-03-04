/**
 * Food Rescue - Core Application Logic
 * Handles: Auth State, API Calls, UI Transitions
 */

const CONFIG = {
    BASE_API: 'http://localhost/food-rescue-api'
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

        if (currentView === 'dashboard' && user) {
            this.nav.style.display = 'none';
            this.renderDashboard();
            return;
        }

        this.nav.style.display = 'block';
        // Navigation Bar State
        this.nav.innerHTML = `
            <div class="container" style="display:flex; justify-content:space-between; align-items:center; width:100%; height:100%">
                <div class="logo" data-link="hero" style="cursor:pointer">
                    🍱 FoodRescue
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
                    <h1>Bridging <span class="gradient-text">Food Waste</span><br>to Hungry Hearts</h1>
                    <p style="font-size: 1.25rem; color: var(--text-muted); max-width: 600px; margin-bottom: 40px">
                        A real-time platform connecting donors, NGOs, and volunteers to rescue surplus food and serve those in need. Join the mission today.
                    </p>
                    <div style="display: flex; gap: 20px">
                        <button class="btn btn-primary" data-link="register">Become a Hero</button>
                        <button class="btn btn-outline" data-link="login">Find Food Near Me</button>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/hero_banner.png" alt="Food Rescue Illustration">
                </div>
            </section>

            <!-- Features Section -->
            <section class="section-padding glass" style="margin-top: 100px">
                <div class="container">
                    <div class="text-center" style="margin-bottom: 60px">
                        <h2 style="font-size: 2.5rem; margin-bottom: 15px">Why Choose Food Rescue?</h2>
                        <p style="color: var(--text-muted)">We provide the infrastructure for a zero-waste world.</p>
                    </div>
                    <div class="feature-grid">
                        <div class="glass-card">
                            <div class="feature-icon">🛡️</div>
                            <h3>Verified Security</h3>
                            <p style="color:var(--text-muted)">All donors and NGOs are verified members of our trusted community network.</p>
                        </div>
                        <div class="glass-card">
                            <div class="feature-icon">⚡</div>
                            <h3>Real-time Alerts</h3>
                            <p style="color:var(--text-muted)">Get instant notifications when fresh surplus food is available in your local area.</p>
                        </div>
                        <div class="glass-card">
                            <div class="feature-icon">📍</div>
                            <h3>Smart Routing</h3>
                            <p style="color:var(--text-muted)">Our platform calculates the fastest rescue routes to minimize transit time.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <section class="section-padding container">
                <div class="glass stats-banner">
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">12k+</h3>
                        <p>Meals Served</p>
                    </div>
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">850</h3>
                        <p>Active Donors</p>
                    </div>
                    <div class="stat-item text-center">
                        <h3 class="gradient-text">4.2t</h3>
                        <p>Food Rescued</p>
                    </div>
                </div>
            </section>

            <!-- How it Works -->
            <section class="section-padding">
                <div class="container">
                    <div class="text-center" style="margin-bottom: 80px">
                        <h2 style="font-size: 2.5rem; margin-bottom: 15px">How It Works</h2>
                        <p style="color: var(--text-muted)">Three simple steps to make a massive impact.</p>
                    </div>
                    <div class="steps-container">
                        <div class="step-card animate-fade">
                            <div class="step-number">01</div>
                            <div class="glass-card" style="width: 100%">
                                <h3>Donor Posts Alert</h3>
                                <p style="color:var(--text-muted)">Restaurants, hotels, or individuals post details about surplus fresh food via the app.</p>
                            </div>
                        </div>
                        <div class="step-card animate-fade">
                            <div class="step-number">02</div>
                            <div class="glass-card" style="width: 100%">
                                <h3>NGOs Get Notified</h3>
                                <p style="color:var(--text-muted)">Verified NGOs within a tight radius receive an immediate push notification alert.</p>
                            </div>
                        </div>
                        <div class="step-card animate-fade">
                            <div class="step-number">03</div>
                            <div class="glass-card" style="width: 100%">
                                <h3>Swift Rescue</h3>
                                <p style="color:var(--text-muted)">NGO volunteers claim the alert and pick up the food for immediate distribution.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `;
    },
    renderLogin() {
        this.app.innerHTML = `
            <div class="container animate-fade" style="display: flex; justify-content: center; padding: 100px 0">
                <div class="glass-card" style="width: 100%; max-width: 400px">
                    <h2 style="margin-bottom: 20px">Welcome Back</h2>
                    <form id="login-form">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="name@example.com">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center">Login</button>
                    </form>
                    <p style="margin-top:20px; text-align:center; font-size:0.9rem; color:var(--text-muted)">
                        New here? <a href="#" data-link="register" style="color:var(--primary)">Create account</a>
                    </p>
                </div>
            </div>
        `;
        document.getElementById('login-form').onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            this.login(Object.fromEntries(formData));
        };
    },

    renderRegister() {
        this.app.innerHTML = `
            <div class="container animate-fade" style="display: flex; justify-content: center; padding: 50px 0">
                <div class="glass-card" style="width: 100%; max-width: 500px">
                    <h2 style="margin-bottom: 20px">Join the Mission</h2>
                    <form id="register-form">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" required placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="john@example.com">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" required placeholder="••••••••">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role" required>
                                    <option value="DONOR">Donor</option>
                                    <option value="NGO">NGO</option>
                                    <option value="VOLUNTEER">Volunteer</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" placeholder="9876543210">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center">Create Account</button>
                    </form>
                    <p style="margin-top:20px; text-align:center; font-size:0.9rem; color:var(--text-muted)">
                        Already a hero? <a href="#" data-link="login" style="color:var(--primary)">Login here</a>
                    </p>
                </div>
            </div>
        `;
        document.getElementById('register-form').onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            this.register(Object.fromEntries(formData));
        };
    },

    async renderDashboard() {
        const { user, currentTab } = this.state;
        if (!user) return this.navigateTo('login');

        this.app.innerHTML = `
            <div class="dashboard-container">
                ${this.renderSidebar()}
                <main class="main-content">
                    <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px">
                        <div>
                            <h2 style="font-size: 2rem">${currentTab.replace('_', ' ').toUpperCase()}</h2>
                            <p style="color:var(--text-muted)">Project Food Rescue Active Sessions</p>
                        </div>
                        <div style="display:flex; gap:15px">
                             <div class="glass-card" style="padding:10px 20px; font-size:0.9rem">
                                📍 ${user.latitude ? 'Location Active' : 'Location Not Set'}
                             </div>
                             <button class="btn btn-outline" onclick="App.logout()">Logout</button>
                        </div>
                    </header>
                    <div id="dashboard-content" class="animate-fade">
                        <p>Loading view...</p>
                    </div>
                </main>
            </div>
        `;

        this.renderTabContent(currentTab);
    },

    renderSidebar() {
        const { user, currentTab } = this.state;
        let menu = [];

        if (user.role === 'ADMIN') {
            menu = [
                { id: 'admin_overview', label: '📊 Overview', icon: '📊' },
                { id: 'admin_users', label: '👥 User Management', icon: '👥' },
                { id: 'admin_alerts', label: '📦 Alert Monitor', icon: '📦' },
                { id: 'admin_reports', label: '📄 Reports', icon: '📄' }
            ];
        } else if (user.role === 'DONOR') {
            menu = [
                { id: 'donor_overview', label: '📊 My Impact', icon: '📊' },
                { id: 'donor_create', label: '➕ Create Alert', icon: '➕' },
                { id: 'donor_history', label: '📜 History', icon: '📜' }
            ];
        } else if (user.role === 'NGO' || user.role === 'VOLUNTEER') {
            menu = [
                { id: 'ngo_overview', label: '📍 Live Alerts', icon: '📍' },
                { id: 'ngo_claims', label: '🤝 My Claims', icon: '🤝' },
                { id: 'ngo_history', label: '📜 History', icon: '📜' }
            ];
        }

        return `
            <aside class="sidebar">
                <div class="logo" data-link="hero" style="font-size: 1.5rem; margin-bottom: 20px; cursor:pointer">
                    🍱 FoodRescue
                </div>
                <div style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 30px">
                    <div style="font-weight: 700; font-size: 0.9rem">${user.name}</div>
                    <div class="badge badge-available" style="font-size: 0.6rem; margin-top: 5px">${user.role}</div>
                </div>
                <nav class="sidebar-nav">
                    ${menu.map(item => `
                        <div class="nav-item ${currentTab === item.id ? 'active' : ''}" onclick="App.switchTab('${item.id}')">
                            <span>${item.icon}</span>
                            <span>${item.label}</span>
                        </div>
                    `).join('')}
                </nav>
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
            case 'donor_create':
                this.renderCreateAlertInline(content);
                break;
            case 'donor_overview':
            case 'donor_history':
                this.loadDonorAlerts(content);
                break;
            case 'ngo_overview':
                this.loadNearbyAlerts(content);
                break;
            default:
                content.innerHTML = `<div class="glass-card text-center" style="padding:100px">
                    <h3>Tab "${tab.replace('_', ' ')}" Under Development</h3>
                    <p style="color:var(--text-muted)">This feature will be available in the next update.</p>
                </div>`;
        }
    },

    renderCreateAlertInline(container) {
        container.innerHTML = `
            <div class="glass-card animate-fade" style="max-width:600px; margin:0 auto">
                <h3 style="margin-bottom:20px">New Food Rescue Alert</h3>
                <form id="alert-form-inline">
                    <input type="hidden" name="donor_id" value="${this.state.user.user_id}">
                    <div class="form-group">
                        <label>What are you donating?</label>
                        <input type="text" name="food_type" required placeholder="e.g. 50 Packets of Biryani">
                    </div>
                    <div class="form-group">
                        <label>Total Quantity</label>
                        <input type="text" name="quantity" required placeholder="e.g. 20kg / 50 Portions">
                    </div>
                    <div class="form-group">
                        <label>Best Before / Expiry</label>
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
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:10px">Broadcast to Local NGOs</button>
                </form>
            </div>
        `;

        document.getElementById('alert-form-inline').onsubmit = async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            data.expiry_time = data.expiry_time.replace('T', ' ') + ':00';

            const res = await this.apiPost('/alerts/create_alert.php', data);
            if (res.success) {
                this.showToast('Alert live! Waiting for NGO claim.', 'success');
                this.switchTab('donor_history');
            } else {
                this.showToast(res.message, 'error');
            }
        };
    },

    async loadAdminStats(container) {
        container.innerHTML = '<p>Loading system intelligence...</p>';
        try {
            const res = await this.apiGet('/admin/get_stats.php');
            if (res.success) {
                const s = res.data;
                container.innerHTML = `
                    <div class="stats-grid">
                        <div class="glass-card stat-card">
                            <span class="label">Total Platform Users</span>
                            <span class="value gradient-text">${s.total_users}</span>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Live Active Alerts</span>
                            <span class="value" style="color:#10b981">${s.active_alerts}</span>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Completed Rescues</span>
                            <span class="value" style="color:#8b5cf6">${s.completed_rescues}</span>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">30-Day Volume</span>
                            <span class="value" style="color:#3b82f6">${s.monthly_alerts}</span>
                        </div>
                    </div>
                    <div class="glass-card">
                        <div class="table-header">
                            <h3>Real-time Growth Trend</h3>
                        </div>
                        <div class="chart-placeholder">
                            [ System Analytics Visualization - Integration in Progress ]
                        </div>
                    </div>
                `;
            }
        } catch (e) { container.innerHTML = '<p>Error loading stats.</p>'; }
    },

    async loadAdminReports(container) {
        container.innerHTML = `
            <div class="glass-card">
                <h3 style="margin-bottom:20px">System Reports & Controls</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px">
                    <div class="glass-card" style="padding:20px; background:rgba(255,255,255,0.02)">
                        <h4>📈 Analytics Export</h4>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin:10px 0">Download full platform data for external processing.</p>
                        <button class="btn btn-outline" style="width:100%; justify-content:center">Generate CSV Report</button>
                    </div>
                    <div class="glass-card" style="padding:20px; background:rgba(255,255,255,0.02)">
                        <h4>🔔 Global Broadcast</h4>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin:10px 0">Send an emergency notification to all active NGOs.</p>
                        <button class="btn btn-primary" style="width:100%; justify-content:center">Send Announcement</button>
                    </div>
                </div>
                <div class="glass-card" style="padding:20px; background:rgba(255,255,255,0.02)">
                    <h4>📅 Date Range Filtering</h4>
                    <div style="display:flex; gap:15px; margin-top:15px">
                        <input type="date" class="form-group" style="padding:10px; flex:1">
                        <input type="date" class="form-group" style="padding:10px; flex:1">
                        <button class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </div>
        `;
    },

    async loadDonorOverview(container) {
        container.innerHTML = '<p>Calculating your positive impact...</p>';
        try {
            const res = await this.apiGet(`/alerts/get_donor_stats.php?donor_id=${this.state.user.user_id}`);
            if (res.success) {
                const s = res.data;
                container.innerHTML = `
                    <div class="stats-grid">
                        <div class="glass-card stat-card">
                            <span class="label">Total Contributions</span>
                            <span class="value gradient-text">${s.total_donations}</span>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Rescued & Completed</span>
                            <span class="value" style="color:#10b981">${s.completed_rescues}</span>
                        </div>
                        <div class="glass-card stat-card">
                            <span class="label">Lives Impacted (Est.)</span>
                            <span class="value" style="color:#f59e0b">${s.completed_rescues * 10}</span>
                        </div>
                    </div>
                    <div style="margin-top:40px">
                        <h3 style="margin-bottom:20px">Active Alerts</h3>
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
                container.innerHTML = `
                    <div class="glass-card table-card">
                        <div class="table-header">
                            <h3>Active Directory</h3>
                            <button class="btn btn-outline" style="font-size:0.8rem">Export CSV</button>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${res.data.users.map(u => `
                                        <tr>
                                            <td><strong>${u.NAME}</strong></td>
                                            <td style="color:var(--text-muted)">${u.EMAIL}</td>
                                            <td><span class="badge" style="background:rgba(255,255,255,0.05)">${u.ROLE}</span></td>
                                            <td>${u.JOINED}</td>
                                            <td><span class="badge badge-${u.STATUS.toLowerCase()}">${u.STATUS}</span></td>
                                            <td>
                                                <button class="btn btn-outline" style="padding:5px 10px; font-size:0.7rem" 
                                                    onclick="App.toggleUserStatus(${u.USER_ID}, '${u.STATUS === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE'}')">
                                                    ${u.STATUS === 'ACTIVE' ? 'Suspend' : 'Activate'}
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
        } catch (e) { container.innerHTML = '<p>Error loading users.</p>'; }
    },

    async toggleUserStatus(userId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus === 'ACTIVE' ? 'activate' : 'suspend'} this account?`)) return;
        try {
            const res = await this.apiPost('/admin/toggle_user_status.php', { user_id: userId, new_status: newStatus });
            if (res.success) {
                this.showToast(res.message, 'success');
                this.renderDashboard();
            } else { this.showToast(res.message, 'error'); }
        } catch (e) { this.showToast('Network error updating status.', 'error'); }
    },

    async loadAdminAlerts(container) {
        container.innerHTML = '<p>Accessing global food inventory...</p>';
        try {
            const res = await this.apiGet('/admin/manage_alerts.php');
            if (res.success) {
                container.innerHTML = `
                    <div class="glass-card table-card">
                        <div class="table-header">
                            <h3>Alert Inventory</h3>
                            <button class="btn btn-outline" style="font-size:0.8rem">Cleanup Expired</button>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Donor</th>
                                        <th>Quantity</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${res.data.alerts.map(a => `
                                        <tr>
                                            <td><strong>${a.FOOD_TYPE}</strong></td>
                                            <td>${a.DONOR_NAME}</td>
                                            <td>${a.QUANTITY}</td>
                                            <td style="color:var(--text-muted); font-size:0.85rem">${a.EXPIRY}</td>
                                            <td><span class="badge badge-${a.STATUS.toLowerCase()}">${a.STATUS}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
        } catch (e) { container.innerHTML = '<p>Error loading alerts.</p>'; }
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
            // Format datetime for Oracle
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
    }
};

window.App = App;
document.addEventListener('DOMContentLoaded', () => App.init());
