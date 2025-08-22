<template>
<div class="login-container" id="loginPage">
    <div class="auth-container">
        <!-- Login Form -->
        <div v-if="currentView === 'login'" class="auth-wrapper">
            <div class="left-section">
                <div class="brand-section">
                    <div class="logo-container">
                        <img src="/img/logo.png" alt="NetMap Logo" class="network-img">
                    </div>
                    <div class="brand-text">
                        <h1>NET<span>MAP</span></h1>
                        <p>Sistem Pemetaan Jaringan Terintegrasi</p>
                    </div>
                    <div class="decorative-elements">
                        <div class="circle circle-1"></div>
                        <div class="circle circle-2"></div>
                        <div class="circle circle-3"></div>
                    </div>
                </div>
            </div>
                    
            <div class="right-section">
                <div class="auth-form">
                    <div class="form-header">
                        <h2>Selamat Datang</h2>
                        <p>Masukkan kredensial Anda untuk mengakses sistem</p>
                    </div>
                    
                    <form @submit.prevent="handleLogin" class="form">
                        <div class="form-group">
                            <label for="username">Username atau Email</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-user"></i>
                                <input 
                                    type="text" 
                                    id="username"
                                    v-model="loginForm.username"
                                    placeholder="Masukkan username atau email"
                                    class="form-input"
                                    required
                                />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-lock"></i>
                                <input 
                                    type="password" 
                                    id="password"
                                    v-model="loginForm.password"
                                    placeholder="Masukkan password"
                                    class="form-input"
                                    required
                                />
                                <button type="button" class="password-toggle" @click="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" v-model="rememberMe">
                                <span class="checkmark"></span>
                                Ingat saya
                            </label>
                            <a href="#" class="forgot-link">Lupa password?</a>
                        </div>
                        
                        <button type="submit" class="btn-primary" :disabled="loading">
                            <i class="fas fa-sign-in-alt"></i>
                            Masuk
                            <span v-if="loading" class="loader"></span>
                        </button>
                        
                        <div class="divider">
                            <span>atau</span>
                        </div>
                        
                        <p class="switch-form">
                            Belum punya akun? 
                            <button type="button" @click="currentView = 'register'" class="link-button">
                                Daftar sekarang
                            </button>
                        </p>
                    </form>
                    
                    <div v-if="errorMessage" class="alert error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ errorMessage }}
                    </div>
                    <div v-if="successMessage" class="alert success-alert">
                        <i class="fas fa-check-circle"></i>
                        {{ successMessage }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Register Form -->
        <div v-if="currentView === 'register'" class="auth-wrapper">
            <div class="left-section">
                <div class="brand-section">
                    <div class="logo-container">
                        <img src="/img/logo.png" alt="NetMap Logo" class="network-img">
                    </div>
                    <div class="brand-text">
                        <h1>NET<span>MAP</span></h1>
                        <p>Bergabunglah dengan sistem pemetaan terdepan</p>
                    </div>
                    <div class="decorative-elements">
                        <div class="circle circle-1"></div>
                        <div class="circle circle-2"></div>
                        <div class="circle circle-3"></div>
                    </div>
                </div>
            </div>
        
            <div class="right-section">
                <div class="auth-form">
                    <div class="form-header">
                        <h2>Buat Akun Baru</h2>
                        <p>Lengkapi data diri untuk membuat akun</p>
                    </div>
                    
                    <form @submit.prevent="handleRegister" class="form">
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-user-circle"></i>
                                <input 
                                    type="text" 
                                    id="name"
                                    v-model="registerForm.name"
                                    placeholder="Masukkan nama lengkap"
                                    class="form-input"
                                    required
                                />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg-username">Username</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-at"></i>
                                <input 
                                    type="text" 
                                    id="reg-username"
                                    v-model="registerForm.username"
                                    placeholder="Pilih username unik"
                                    class="form-input"
                                    required
                                />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-envelope"></i>
                                <input 
                                    type="email" 
                                    id="email"
                                    v-model="registerForm.email"
                                    placeholder="alamat@email.com"
                                    class="form-input"
                                    required
                                />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg-password">Password</label>
                            <div class="input-wrapper">
                                <i class="input-icon fas fa-lock"></i>
                                <input 
                                    type="password" 
                                    id="reg-password"
                                    v-model="registerForm.password"
                                    placeholder="Buat password yang kuat"
                                    class="form-input"
                                    required
                                />
                                <button type="button" class="password-toggle" @click="togglePassword('reg-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" required>
                                <span class="checkmark"></span>
                                Saya setuju dengan <a href="#" class="terms-link">syarat dan ketentuan</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-primary" :disabled="loading">
                            <i class="fas fa-user-plus"></i>
                            Daftar Sekarang
                            <span v-if="loading" class="loader"></span>
                        </button>
                        
                        <div class="divider">
                            <span>atau</span>
                        </div>
                        
                        <p class="switch-form">
                            Sudah punya akun? 
                            <button type="button" @click="currentView = 'login'" class="link-button">
                                Masuk di sini
                            </button>
                        </p>
                    </form>
                    
                    <div v-if="errorMessage" class="alert error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ errorMessage }}
                    </div>
                    <div v-if="successMessage" class="alert success-alert">
                        <i class="fas fa-check-circle"></i>
                        {{ successMessage }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      currentView: 'login',
      rememberMe: false,
      loading: false,
      errorMessage: '',
      successMessage: '',
      // Perbaiki baseURL - jangan gunakan port 8000
      baseUrl: 'http://localhost',
      
      loginForm: {
        username: '',
        password: ''
      },
      registerForm: {
        name: '',
        username: '',
        email: '',
        password: ''
      }
    }
  },
  
  methods: {
    async handleLogin() {
      const loginData = {
        username: this.username,
        password: this.password
      };

      try {
        const response = await fetch("http://localhost:8000/backend/api/auth/login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(loginData)
        });

        const result = await response.json();

        if (result.status === "success") {
          // Simpan user ke localStorage
          localStorage.setItem("userData", JSON.stringify(result.data));

          alert("Login berhasil!");
          this.$router.push("/project"); // pindah ke halaman Project
        } else {
          alert("Login gagal: " + result.message);
        }
      } catch (error) {
        console.error("Login error:", error);
        alert("Error: " + error.message);
      }
    },
  

    // Clear messages
    clearMessages() {
      this.errorMessage = '';
      this.successMessage = '';
    },

    // Show error message
    showError(message) {
      this.errorMessage = message;
      this.successMessage = '';
      setTimeout(() => {
        this.errorMessage = '';
      }, 5000);
    },

    // Show success message
    showSuccess(message) {
      this.successMessage = message;
      this.errorMessage = '';
      setTimeout(() => {
        this.successMessage = '';
      }, 3000);
    },

    // Generic API call method
    async apiCall(endpoint, method = 'POST', data = null) {
      this.loading = true;
      this.clearMessages();
      
      try {
        const config = {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        };

        if (data) {
          config.body = JSON.stringify(data);
        }

        const fullUrl = `${this.baseUrl}${endpoint}`;
        console.log('Making API call to:', fullUrl);
        console.log('Request data:', data);

        const response = await fetch(fullUrl, config);
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        const responseText = await response.text();
        console.log('RAW RESPONSE:', responseText);

        if (!responseText.trim()) {
          throw new Error('Empty response from server');
        }

        if (responseText.trim().startsWith('<')) {
          console.error('Server returned HTML:', responseText);
          throw new Error('Server returned HTML error page instead of JSON');
        }

        if (responseText.includes('Parse error') || responseText.includes('Fatal error')) {
          console.error('PHP Error:', responseText);
          throw new Error('Server PHP error occurred');
        }

        const result = JSON.parse(responseText);
        console.log('Parsed response:', result);
        return result;

      } catch (error) {
        console.error('API Error:', error);
        this.showError(`Connection error: ${error.message}`);
        return { success: false, error: error.message };
      } finally {
        this.loading = false;
      }
    },

    // Handle login
    async handleLogin() {
      if (!this.loginForm.username || !this.loginForm.password) {
        this.showError('Username dan password harus diisi');
        return;
      }

      if (this.loginForm.username.length < 3) {
        this.showError('Username minimal 3 karakter');
        return;
      }

      try {
        // Gunakan path endpoint yang benar
        const result = await this.apiCall('/project_map/backend/api/auth/login.php', 'POST', {
          username: this.loginForm.username.trim(),
          password: this.loginForm.password
        });

        if (result && result.status === 'success') {
          this.showSuccess('Login berhasil! Mengalihkan ke dashboard...');
          
          localStorage.setItem('user', JSON.stringify(result.data));
          localStorage.setItem('token', 'logged_in');
          localStorage.setItem('userId', result.data.id_user);
          
          this.loginForm = { username: '', password: '' };
          
          setTimeout(() => {
            this.$router.push('/dashboard');
          }, 1500);
          
        } else {
          this.showError(result.message || 'Login gagal. Periksa username dan password Anda.');
        }
      } catch (error) {
        this.showError('Terjadi kesalahan saat login. Silakan coba lagi.');
        console.error('Login error:', error);
      }
    },

    // Handle register
    async handleRegister() {
      if (!this.registerForm.name || !this.registerForm.username || 
          !this.registerForm.email || !this.registerForm.password) {
        this.showError('Semua field harus diisi');
        return;
      }

      if (this.registerForm.name.length < 2) {
        this.showError('Nama minimal 2 karakter');
        return;
      }

      if (this.registerForm.username.length < 3) {
        this.showError('Username minimal 3 karakter');
        return;
      }

      if (this.registerForm.password.length < 6) {
        this.showError('Password minimal 6 karakter');
        return;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.registerForm.email)) {
        this.showError('Format email tidak valid');
        return;
      }

      try {
        // Gunakan path endpoint yang benar
        const result = await this.apiCall('/project_map/backend/api/auth/register.php', 'POST', {
          nama: this.registerForm.name.trim(),
          username: this.registerForm.username.trim(),
          email: this.registerForm.email.trim(),
          password: this.registerForm.password
        });

        if (result && result.status === 'success') {
          this.showSuccess('Registrasi berhasil! Silakan login dengan akun Anda.');
          
          this.registerForm = { name: '', username: '', email: '', password: '' };
          
          setTimeout(() => {
            this.currentView = 'login';
            this.clearMessages();
          }, 2000);
          
        } else {
          this.showError(result.message || 'Registrasi gagal. Username atau email mungkin sudah terdaftar.');
        }
      } catch (error) {
        this.showError('Terjadi kesalahan saat registrasi. Silakan coba lagi.');
        console.error('Register error:', error);
      }
    },

    // Toggle password visibility
    togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = field.nextElementSibling.querySelector('i');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  },

  // Check if user is already logged in
  mounted() {
    const token = localStorage.getItem('token');
    const user = localStorage.getItem('user');
    
    if (token && user) {
      this.$router.push('/dashboard');
    }
  }
}
</script>

<style scoped>
/* Main Container */
.login-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  min-height: 100vh;
  background: linear-gradient(135deg, #E5EEF1 0%, #CCD2DE 100%);
}

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: #FFFF;
}

.auth-wrapper {
  display: flex;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(23, 103, 126, 0.15);
  overflow: hidden;
  max-width: 1200px;
  width: 100%;
  min-height: 600px;
}

/* Left Section */
.left-section {
  flex: 1.2;
  background: linear-gradient(135deg, #17677E 0%, #2980b9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 60px 40px;
  overflow: hidden;
}

.brand-section {
  text-align: center;
  z-index: 2;
  position: relative;
}

.logo-container {
  margin-bottom: 30px;
}

.network-img {
  max-width: 180px;
  height: auto;
  border-radius: 15px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}

.network-img:hover {
  transform: scale(1.05);
}

.brand-text h1 {
  font-size: 3.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.brand-text span {
  color: #E5EEF1;
}

.brand-text p {
  color: #CCD2DE;
  font-size: 1.1rem;
  margin-top: 10px;
  font-weight: 300;
}

/* Decorative Elements */
.decorative-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(229, 238, 241, 0.1);
  animation: float 6s ease-in-out infinite;
}

.circle-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  right: 10%;
  animation-delay: 0s;
}

.circle-2 {
  width: 60px;
  height: 60px;
  bottom: 30%;
  left: 15%;
  animation-delay: 2s;
}

.circle-3 {
  width: 80px;
  height: 80px;
  top: 60%;
  right: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-20px) scale(1.1); }
}

/* Right Section */
.right-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 40px;
  background: white;
}

.auth-form {
  width: 100%;
  max-width: 400px;
}

.form-header {
  text-align: center;
  margin-bottom: 40px;
}

.form-header h2 {
  color: #17677E;
  font-size: 2.2rem;
  font-weight: 600;
  margin: 0 0 10px 0;
}

.form-header p {
  color: #666;
  font-size: 1rem;
  margin: 0;
}

/* Form Styles */
.form {
  width: 100%;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  color: #17677E;
  font-weight: 500;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 15px;
  color: #CCD2DE;
  z-index: 2;
  font-size: 1.1rem;
}

.form-input {
  width: 100%;
  padding: 15px 15px 15px 45px;
  border: 2px solid #E5EEF1;
  border-radius: 12px;
  font-size: 1rem;
  background: white;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #17677E;
  box-shadow: 0 0 0 3px rgba(23, 103, 126, 0.1);
}

.form-input::placeholder {
  color: #CCD2DE;
}

.password-toggle {
  position: absolute;
  right: 15px;
  background: none;
  border: none;
  color: #CCD2DE;
  cursor: pointer;
  padding: 5px;
  z-index: 2;
  transition: color 0.3s ease;
}

.password-toggle:hover {
  color: #17677E;
}

/* Form Options */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  font-size: 0.9rem;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #666;
}

.checkbox-wrapper input {
  display: none;
}

.checkmark {
  width: 18px;
  height: 18px;
  border: 2px solid #CCD2DE;
  border-radius: 4px;
  margin-right: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.checkbox-wrapper input:checked + .checkmark {
  background: #17677E;
  border-color: #17677E;
}

.checkbox-wrapper input:checked + .checkmark::after {
  content: '✓';
  color: white;
  font-size: 0.8rem;
}

.forgot-link, .terms-link {
  color: #17677E;
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover, .terms-link:hover {
  text-decoration: underline;
}

/* Buttons */
.btn-primary {
  width: 100%;
  background: linear-gradient(135deg, #17677E 0%, #2980b9 100%);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(23, 103, 126, 0.3);
}

.btn-primary:active {
  transform: translateY(0);
}

/* Divider */
.divider {
  text-align: center;
  margin: 30px 0;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #E5EEF1;
}

.divider span {
  background: white;
  padding: 0 20px;
  color: #CCD2DE;
  font-size: 0.9rem;
  position: relative;
}

/* Switch Form */
.switch-form {
  text-align: center;
  color: #666;
  margin: 0;
}

.link-button {
  background: none;
  border: none;
  color: #17677E;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  padding: 0;
  font-size: inherit;
}

.link-button:hover {
  text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
  .auth-wrapper {
    flex-direction: column;
    border-radius: 0;
    min-height: 100vh;
  }
  
  .left-section {
    flex: none;
    min-height: 40vh;
    padding: 40px 20px;
  }
  
  .right-section {
    flex: none;
    padding: 40px 20px;
  }
  
  .network-img {
    max-width: 120px;
  }
  
  .brand-text h1 {
    font-size: 2.5rem;
  }
  
  .form-header h2 {
    font-size: 1.8rem;
  }
  
  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 0;
  }
  
  .left-section, .right-section {
    padding: 30px 15px;
  }
  
  .brand-text h1 {
    font-size: 2rem;
  }
  
  .form-header h2 {
    font-size: 1.6rem;
  }
}

/* FontAwesome Icons */
.fas {
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
}

.alert {
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.error-alert {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success-alert {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.loader {
  border: 2px solid rgba(255, 255, 255, 0.6);
  border-top: 2px solid rgba(23, 103, 126, 1);
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>