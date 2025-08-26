<template>
<div class="dashboard-container" id="dashboardPage">
  <div class="main">
    <div class="header">
      <div class="logo">
        <i class="fas fa-network-wired"></i>
        <span class="net">NET</span><span class="map">MAP</span>
      </div>
      <i class="fas fa-sign-out-alt logout-icon" @click="logout"></i>
    </div>

    <h1>Dashboard</h1>
    
    <div class="toolbar">
      <button class="btn-new" @click="createNewProject">
        <i class="fas fa-plus"></i> New
      </button>
      
      <div class="filter-section">
        <label for="sortFilter">Sort by:</label>
        <select id="sortFilter" v-model="sortBy" @change="filterProjects" class="filter-select">
          <option value="all">ALL</option>
          <option value="3">3 hari lalu</option>
          <option value="7">7 hari lalu</option>
          <option value="30">1 bulan lalu</option>
        </select>
      </div>
    </div>

    <!-- Projects Table -->
    <div class="table-container">
      <table class="projects-table">
        <thead>
          <tr>
            <th>
              <i class="fas fa-folder"></i> Name
            </th>
            <th>
              <i class="fas fa-user"></i> Owner
            </th>
            <th>
              <i class="fas fa-clock"></i> Last Modified
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading State -->
          <tr v-if="loading">
            <td colspan="4" style="text-align: center; padding: 40px;">
              <i class="fas fa-spinner fa-spin"></i> Loading projects...
            </td>
          </tr>
          
          <!-- Error State -->
          <tr v-else-if="error">
            <td colspan="4" style="text-align: center; padding: 40px; color: red;">
              <i class="fas fa-exclamation-triangle"></i> {{ error }}
              <br><br>
              <button @click="loadProjects()" class="btn-new">
                <i class="fas fa-refresh"></i> Retry
              </button>
            </td>
          </tr>
          
          <!-- Projects Data -->
          <tr v-else-if="projects.length > 0" v-for="project in sortedProjects" :key="project.id" class="project-row">
            <td>
              <div class="project-name">
                <div class="project-icon">
                  <i class="fas fa-folder"></i>
                </div>
                <span class="name-text">{{ project.name }}</span>
              </div>
            </td>
            <td>
              <div class="owner-info">
                <div class="owner-avatar">
                  <i class="fas fa-user-circle"></i>
                </div>
                <span>{{ project.owner }}</span>
              </div>
            </td>
            <td>
              <span class="modified-date">{{ formatDate(project.updated_at) }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn-action btn-edit" @click="editProject(project.id_project)" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-view" @click="viewProject(project.id_project)" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn-action btn-delete" @click="deleteProject(project.id_project)" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          
          <!-- Empty State -->
          <tr v-else class="empty-row">
            <td colspan="4" class="empty-state">
              <div class="empty-content">
                <i class="fas fa-folder-open"></i>
                <h3>No Projects Found</h3>
                <p>Create your first project to get started</p>
                <button class="btn-create-first" @click="createNewProject">
                  <i class="fas fa-plus"></i> Create Project
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Custom Modal -->
  <div id="netmap-prompt-modal" class="netmap-modal-overlay" v-show="showModal">
    <div class="netmap-modal">
      <div class="netmap-modal-header">
        <h2 class="netmap-modal-title">{{ modalTitle }}</h2>
        <p class="netmap-modal-subtitle">{{ modalSubtitle }}</p>
      </div>
      <div class="netmap-modal-body">
        <div v-if="successMessage" class="netmap-success-message">
          {{ successMessage }}
        </div>
        <div class="netmap-input-group">
          <label class="netmap-input-label" for="netmap-project-input">Project Name</label>
          <input 
            type="text" 
            id="netmap-project-input" 
            class="netmap-input"
            :class="{ error: inputError }"
            v-model="projectName"
            @keypress.enter="handleOk"
            placeholder="Enter project name..."
            maxlength="50"
            ref="projectInput"
          >
          <div v-if="inputError" class="netmap-error-message">
            {{ inputError }}
          </div>
        </div>
        <div class="netmap-modal-buttons">
          <button class="netmap-modal-btn netmap-modal-btn-secondary" @click="closeModal">
            Cancel
          </button>
          <button 
            class="netmap-modal-btn netmap-modal-btn-primary"
            :class="{ loading: modalLoading }"
            @click="handleOk"
            :disabled="modalLoading"
          >
            {{ modalLoading ? 'Creating...' : 'Create Project' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {
      sortBy: 'modified',
      projects: [],
      filteredProjects: [],
      loading: false,
      error: null,
      currentUser: null,
      baseUrl: 'http://localhost/project_map/backend',
      
      // Modal data
      showModal: false,
      modalTitle: 'Create New Project',
      modalSubtitle: 'Enter project details below',
      projectName: '',
      inputError: '',
      successMessage: '',
      modalLoading: false,
      modalResolve: null,
      modalReject: null
    }
  },

  async mounted() {
    // Get current user from localStorage
    const userData = localStorage.getItem('user');
    if (userData) {
      this.currentUser = JSON.parse(userData);
      console.log('Current user:', this.currentUser);
    }
    
    await this.loadProjects();
    this.filteredProjects = this.projects;
  },
  
  computed: {
    sortedProjects() {
      const sorted = [...this.projects];
      
      switch(this.sortBy) {
        case 'name':
          return sorted.sort((a, b) => a.name.localeCompare(b.name));
        case 'owner':
          return sorted.sort((a, b) => a.owner.localeCompare(b.owner));
        case 'created':
          return sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        case 'modified':
        default:
          // Sort by updated_at, fallback ke created_at jika updated_at null
          return sorted.sort((a, b) => {
            const dateA = new Date(a.updated_at || a.created_at);
            const dateB = new Date(b.updated_at || b.created_at);
            return dateB - dateA;
          });
      }
    }
  },
  
  methods: {
    async apiCall(endpoint, method = 'GET', data = null) {
      try {
        const config = {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        };

        if (data && method !== 'GET') {
          config.body = JSON.stringify(data);
          console.log('📤 Request data being sent:', data); // Debug log
          console.log('📤 Request body JSON:', JSON.stringify(data)); // Debug log
        }

        const url = `${this.baseUrl}${endpoint}`;
        console.log('🔄 API Call:', method, url);

        const response = await fetch(url, config);
        const responseText = await response.text();
        
        console.log('📄 Raw response:', responseText);
        
        if (!responseText.trim()) {
          throw new Error('Empty response from server');
        }

        // Parse JSON
        const result = JSON.parse(responseText);
        console.log('✅ Parsed result:', result);
        
        return result;
      } catch (error) {
        console.error('💥 API Error:', error);
        return { status: 'error', message: error.message };
      }
    },
    
    async loadProjects() {
      this.loading = true;
      this.error = null;
      
      try {
        console.log('📊 Loading projects...');
        
        // Call the correct API endpoint
        const result = await this.apiCall('/api/project/list.php');
        
        console.log('📋 Load projects result:', result);
        
        if (result.status === 'success') {
          this.projects = result.data || [];
          console.log('✅ Projects loaded:', this.projects);
        } else {
          this.error = result.message || 'Failed to load projects';
          console.error('❌ Load projects error:', this.error);
        }
      } catch (error) {
        this.error = 'Error connecting to server: ' + error.message;
        console.error('💥 Load projects exception:', error);
      } finally {
        this.loading = false;
      }
    },

    // Custom modal methods
    showNetmapPrompt(title = 'Create New Project', subtitle = 'Enter project details below') {
      return new Promise((resolve, reject) => {
        this.modalTitle = title;
        this.modalSubtitle = subtitle;
        this.projectName = '';
        this.inputError = '';
        this.successMessage = '';
        this.modalLoading = false;
        this.showModal = true;
        this.modalResolve = resolve;
        this.modalReject = reject;

        // Focus input after modal is shown
        this.$nextTick(() => {
          if (this.$refs.projectInput) {
            this.$refs.projectInput.focus();
          }
        });
      });
    },

    closeModal() {
      this.showModal = false;
      this.projectName = '';
      this.inputError = '';
      this.successMessage = '';
      this.modalLoading = false;
      
      if (this.modalReject) {
        this.modalReject(null);
        this.modalReject = null;
        this.modalResolve = null;
      }
    },

    handleOk() {
      const value = this.projectName.trim();
      
      if (!value) {
        this.inputError = 'Project name is required';
        return;
      }

      if (value.length < 3) {
        this.inputError = 'Project name must be at least 3 characters';
        return;
      }

      this.inputError = '';
      this.modalLoading = true;

      if (this.modalResolve) {
        this.modalResolve(value);
        this.modalResolve = null;
        this.modalReject = null;
      }

      // Close modal after short delay
      setTimeout(() => {
        this.closeModal();
      }, 500);
    },

    showNetmapSuccess(message = 'Project created successfully!') {
      // Remove any existing success message
      this.successMessage = '';
      
      // Set new success message
      this.successMessage = message;
      
      // Auto hide after 4 seconds
      setTimeout(() => {
        this.successMessage = '';
      }, 4000);
    },

    showNetmapError(message = 'An error occurred!') {
      // Set error message
      this.inputError = message;
      
      // Auto hide after 4 seconds  
      setTimeout(() => {
        this.inputError = '';
      }, 4000);
    },
    
    async createNewProject() {
      if (!this.currentUser) {
        alert('User not logged in');
        return;
      }

      try {
        // Tampilkan custom modal
        const projectName = await this.showNetmapPrompt(
          'Create New Project',
          'Enter a unique name for your project'
        );

        if (!projectName) return;

        // Prepare data - pastikan id_user dikirim dengan benar
        const requestData = {
          nama_project: projectName.trim(),
          id_user: this.currentUser.id_user
        };

        console.log('Sending create project request:', requestData); // Debug log

        // Call API buat project
        const result = await this.apiCall('/api/project/create.php', 'POST', requestData);

        console.log('Create project result:', result); // Debug log

        // Check for result.success
        if (result.success === true) {
          this.showNetmapSuccess(`Project "${projectName}" created successfully!`);

          // Reload projects list to show new project
          await this.loadProjects();

          if (result.data && result.data.id_project) {
            this.currentProject = result.data;
            localStorage.setItem('currentProject', JSON.stringify(result.data));
            this.$router.push(`/project/${result.data.id_project}`);
          } else {
            this.$router.push('/project');
          }
        } else {
          // Tampilkan error dengan style yang bagus
          this.showNetmapError('Gagal membuat project: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        if (error !== null) { // tidak di-cancel
          console.error('Create project error:', error);
          this.showNetmapError('Terjadi kesalahan saat membuat project');
        }
      }
    },

    editProject(projectId) {
      console.log(`Edit project: ${projectId}`);
      this.$router.push(`/project/${projectId}`);
    },
    
    viewProject(projectId) {
      console.log(`View project: ${projectId}`);
      this.$router.push(`/project/${projectId}`);
    },
    
    async deleteProject(projectId) {
      if (!confirm('Are you sure you want to delete this project?')) {
        return;
      }

      try {
        const result = await this.apiCall(`/api/project/delete.php?id=${projectId}`, 'DELETE');

        if (result.status === 'success') {
          await this.loadProjects();
          alert('Project berhasil dihapus!');
        } else {
          alert('Gagal menghapus project: ' + result.message);
        }
      } catch (error) {
        console.error('Error deleting project:', error);
        alert('Terjadi kesalahan saat menghapus project');
      }
    },
    
    moreActions(projectId) {
      console.log(`More actions for project: ${projectId}`);
    },
    
    sortProjects() {
      console.log(`Sorting by: ${this.sortBy}`);
    },
    
    logout() {
      if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        localStorage.removeItem('userId');
        this.$router.push('/');
      }
    },

    filterProjects() {
      if (this.sortBy === "all") {
        this.filteredProjects = this.projects;
        return;
      }

      const days = parseInt(this.sortBy);
      const now = new Date();

      this.filteredProjects = this.projects.filter(project => {
        // Gunakan updated_at sebagai prioritas, fallback ke created_at
        const modifiedDate = project.updated_at ? new Date(project.updated_at) : new Date(project.created_at);
        if (!modifiedDate || isNaN(modifiedDate.getTime())) return false;

        // hitung beda hari
        const diffTime = now - modifiedDate;
        const diffDays = diffTime / (1000 * 60 * 60 * 24);

        return diffDays <= days;
      });
    },
      
    formatDate(date) {
      if (!date) return 'N/A';
      
      const now = new Date();
      let projectDate;
      
      // Handle different date formats
      if (typeof date === 'string') {
        projectDate = new Date(date);
      } else {
        projectDate = date;
      }
      
      // Check if date is valid
      if (isNaN(projectDate.getTime())) {
        return 'Invalid Date';
      }
      
      const diffInMs = now - projectDate;
      const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
      const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
      const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
      
      // Real-time formatting
      if (diffInMinutes < 1) return 'Baru saja';
      if (diffInMinutes < 60) return `${diffInMinutes} menit lalu`;
      if (diffInHours < 24) return `${diffInHours} jam lalu`;
      if (diffInDays === 0) return 'Hari Ini';
      if (diffInDays === 1) return 'Kemarin';
      if (diffInDays < 7) return `${diffInDays} hari lalu`;
      if (diffInDays < 30) return `${Math.floor(diffInDays / 7)} minggu lalu`;
      
      const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      return projectDate.toLocaleDateString('id-ID', options);
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #E5EEF1;
  min-height: 100vh;
  color: #17677E;
}

.dashboard-container {
  display: flex;
  min-height: 100vh;
}

.logo {
  color: #CCD2DE;
  font-size: 28px;
  font-weight: bold;
  text-align: center;
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.logo .net {
  color: #CCD2DE;
}

.logo .map {
  color: #CCD2DE;
}


.logo i {
  margin-right: 8px;
}

.menu-item {
  display: block;
  width: 100%;
  padding: 18px 25px;
  color: #17677E;
  text-decoration: none;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
  border: none;
  background: none;
  cursor: pointer;
  border-left: 4px solid transparent;
}

.menu-item:hover,
.menu-item.active {
  background: rgba(229, 238, 241, 0.1);
  border-left-color: #17677E;
  color: #17677E;
  padding-left: 35px;
}

.menu-item i {
  margin-right: 30px;
  width: 20px;
  text-align: center;
}

.main {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.header {
  width: 100%;
  background: linear-gradient(135deg, #17677E 0%, #145a6b 100%);
  color: #17677E;
  padding: 10px 30px;
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
}

.logout-icon {
  font-size: 15px;
  cursor: pointer;
  padding: 10px 30px;
  border-radius: 50%;
  transition: all 0.3s ease;
  color: #17677E;
  margin-left: auto;
}

.logout-icon:hover {
  background: rgba(229, 238, 241, 0.1);
  transform: scale(1.1);
}

h1 {
  color: #17677E;
  font-size: 30px;
  font-weight: 700;
  margin-bottom: 30px;
  margin-top: 70px;
  margin-left: 20px;
  text-align: left;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.1);
  backdrop-filter: blur(5px);
}

.btn-new {
  padding: 12px 24px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-new:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

.filter-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-section label {
  color: #17677E;
  font-weight: 500;
  font-size: 14px;
}

.filter-select {
  padding: 10px 15px;
  border: 2px solid #CCD2DE;
  border-radius: 8px;
  background: white;
  color: #17677E;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
}

.filter-select:focus {
  outline: none;
  border-color: #17677E;
  box-shadow: 0 0 0 3px rgba(23, 103, 126, 0.2);
}

.table-container {
  background: white;
  border-radius: 15px;
  padding: 0;
  margin: 20px;
  margin-top: 2px;
  box-shadow: 0 8px 25px rgba(23, 103, 126, 0.15);
  overflow: hidden;
  border: 3px;
}

.projects-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.projects-table thead {
  background: #FFFF;
}

.projects-table th {
  padding: 20px;
  text-align: left;
  font-weight: 600;
  color: #17677E;
  font-size: 16px;
  border-bottom: 2px solid #17677E;
}

.projects-table th i {
  margin-right: 8px;
}

.projects-table tbody tr:nth-child(even) {
  background: rgba(229, 238, 241, 0.3);
}

.project-row {
  transition: all 0.3s ease;
  cursor: pointer;
}

.project-row:hover {
  background: rgba(23, 103, 126, 0.05);
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.1);
}

.projects-table td {
  padding: 18px 20px;
  border-bottom: 1px solid rgba(204, 210, 222, 0.3);
  vertical-align: middle;
}

.project-name {
  display: flex;
  align-items: center;
  gap: 12px;
}


.name-text {
  font-weight: 600;
  color: #17677E;
  font-size: 15px;
}

.owner-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.owner-avatar {
  width: 32px;
  height: 32px;
  background: #28a745;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.modified-date {
  color: #666;
  font-size: 14px;
}

/* =============== ACTION BUTTONS =============== */
.action-buttons {
  display: flex;
  gap: 8px;
  opacity: 1;
}

.btn-action {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all 0.3s ease;
}

.btn-edit {
  background: #17677E;
  color: white;
}

.btn-edit:hover {
  background: #145a6b;
  transform: scale(1.1);
}

.btn-view {
  background: #28a745;
  color: white;
}

.btn-view:hover {
  background: #218838;
  transform: scale(1.1);
}

.btn-delete {
  background: #dc3545;
  color: white;
}

.btn-delete:hover {
  background: #c82333;
  transform: scale(1.1);
}

.btn-more {
  background: #6c757d;
  color: white;
}

.btn-more:hover {
  background: #545b62;
  transform: scale(1.1);
}

/* =============== EMPTY STATE =============== */
.empty-row {
  height: 400px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-content {
  color: #17677E;
}

.empty-content i {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-content h3 {
  font-size: 24px;
  margin-bottom: 10px;
  font-weight: 600;
}

.empty-content p {
  font-size: 16px;
  margin-bottom: 25px;
  opacity: 0.7;
}

.btn-create-first {
  padding: 15px 30px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-create-first:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

/* =============== ANIMATIONS =============== */
.dashboard-container {
  animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-container {
  animation: slideUp 0.8s ease-out 0.3s both;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.toolbar {
  animation: slideDown 0.8s ease-out 0.1s both;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* =============== RESPONSIVE DESIGN =============== */
@media (max-width: 1024px) {
  .main {
    margin-left: 220px;
  }
  
  .header {
    left: 220px;
  }
  
  .toolbar {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
}

@media (max-width: 768px) {
  
  .main {
    margin-left: 0;
  }
  
  .header {
    left: 0;
    padding: 15px 20px;
  }
  
  .projects-table {
    font-size: 12px;
  }
  
  .projects-table th,
  .projects-table td {
    padding: 12px 10px;
  }
  
  h1 {
    font-size: 24px;
    margin-left: 10px;
  }
}

@media (max-width: 480px) {
  .table-container {
    margin: 10px;
    border-radius: 10px;
  }
  
  .projects-table th:nth-child(3),
  .projects-table td:nth-child(3) {
    display: none;
  }
  
  .btn-action {
    width: 28px;
    height: 28px;
    font-size: 10px;
  }
}

button:focus,
select:focus {
  outline: 2px solid #17677E;
  outline-offset: 2px;
}

/* CSS untuk mempercantik tampilan existing elements di Dashboard NETMAP */

/* Perbaikan untuk tombol New yang sudah ada */
.btn-new {
  padding: 12px 24px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.25);
  display: flex;
  align-items: center;
  gap: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
  overflow: hidden;
}

.btn-new::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s ease;
}

.btn-new:hover::before {
  left: 100%;
}

.btn-new:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 10px 30px rgba(40, 167, 69, 0.35);
}

.btn-new:active {
  transform: translateY(-1px) scale(0.98);
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-new i {
  font-size: 14px;
  transition: transform 0.3s ease;
}

.btn-new:hover i {
  transform: rotate(90deg);
}

/* Enhanced Table Styling */
.projects-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 14px;
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(23, 103, 126, 0.12);
}

.projects-table thead {
  background: linear-gradient(135deg, #17677E 0%, #145a6b 100%);
}

.projects-table th {
  padding: 25px 20px;
  text-align: left;
  font-weight: 700;
  color: white;
  font-size: 15px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  position: relative;
}

.projects-table th::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
}

.projects-table th i {
  margin-right: 8px;
  font-size: 16px;
}

/* Enhanced Row Styling */
.projects-table tbody tr {
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  border-bottom: 1px solid rgba(204, 210, 222, 0.2);
}

.projects-table tbody tr:nth-child(even) {
  background: linear-gradient(90deg, rgba(229, 238, 241, 0.3), rgba(229, 238, 241, 0.1));
}

.projects-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(23, 103, 126, 0.08), rgba(23, 103, 126, 0.03));
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(23, 103, 126, 0.15);
  border-radius: 8px;
}

.projects-table td {
  padding: 20px;
  vertical-align: middle;
  position: relative;
}

/* Project Name Styling */
.project-name {
  display: flex;
  align-items: center;
  gap: 15px;
}

.project-icon {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #17677E, #20c997);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(23, 103, 126, 0.2);
}

.project-row:hover .project-icon {
  transform: rotate(5deg) scale(1.1);
  box-shadow: 0 6px 18px rgba(23, 103, 126, 0.3);
}

.name-text {
  font-weight: 700;
  color: #17677E;
  font-size: 16px;
  transition: color 0.3s ease;
}

.project-row:hover .name-text {
  color: #145a6b;
}

/* Owner Info Styling */
.owner-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.owner-avatar {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, #28a745, #20c997);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
}

.project-row:hover .owner-avatar {
  transform: scale(1.1);
  box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

/* Enhanced Action Buttons */
.action-buttons {
  display: flex;
  gap: 8px;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.project-row:hover .action-buttons {
  opacity: 1;
}

.btn-action {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-action::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.3s ease;
}

.btn-action:hover::before {
  width: 100px;
  height: 100px;
}

.btn-edit {
  background: linear-gradient(135deg, #17677E, #145a6b);
  color: white;
  box-shadow: 0 4px 12px rgba(23, 103, 126, 0.3);
}

.btn-edit:hover {
  background: linear-gradient(135deg, #145a6b, #0f4a57);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 18px rgba(23, 103, 126, 0.4);
}

.btn-view {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-view:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 18px rgba(40, 167, 69, 0.4);
}

.btn-delete {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-delete:hover {
  background: linear-gradient(135deg, #c82333, #a71e2a);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 18px rgba(220, 53, 69, 0.4);
}

/* Modified Date Styling */
.modified-date {
  color: #666;
  font-size: 14px;
  font-weight: 500;
  padding: 6px 12px;
  background: rgba(23, 103, 126, 0.05);
  border-radius: 20px;
  transition: all 0.3s ease;
}

.project-row:hover .modified-date {
  background: rgba(23, 103, 126, 0.1);
  color: #17677E;
}

/* Enhanced Toolbar */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding: 25px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
  border-radius: 20px;
  box-shadow: 
    0 10px 30px rgba(23, 103, 126, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Enhanced Filter Section */
.filter-section {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 5px;
}

.filter-section label {
  color: #17677E;
  font-weight: 600;
  font-size: 15px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-select {
  padding: 12px 18px;
  border: 2px solid rgba(23, 103, 126, 0.2);
  border-radius: 12px;
  background: white;
  color: #17677E;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  min-width: 140px;
  box-shadow: 0 4px 15px rgba(23, 103, 126, 0.1);
}

.filter-select:focus {
  outline: none;
  border-color: #17677E;
  box-shadow: 0 0 0 4px rgba(23, 103, 126, 0.1);
  transform: translateY(-2px);
}

.filter-select:hover {
  border-color: #17677E;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(23, 103, 126, 0.15);
}

/* Loading State Enhancement */
.projects-table tbody tr td[colspan="4"] {
  text-align: center;
  padding: 60px 20px;
  color: #17677E;
  font-size: 16px;
  font-weight: 500;
}

.fa-spinner {
  animation: spin 1s linear infinite;
  margin-right: 10px;
  font-size: 20px;
  color: #17677E;
}

/* Empty State Enhancement */
.empty-content {
  color: #17677E;
  padding: 40px;
}

.empty-content i {
  font-size: 80px;
  margin-bottom: 25px;
  opacity: 0.4;
  color: #CCD2DE;
}

.empty-content h3 {
  font-size: 28px;
  margin-bottom: 15px;
  font-weight: 700;
  color: #17677E;
}

.empty-content p {
  font-size: 18px;
  margin-bottom: 30px;
  opacity: 0.8;
  line-height: 1.5;
}

.btn-create-first {
  padding: 15px 30px;
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-create-first:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
}

/* Responsive Enhancements */
@media (max-width: 768px) {
  .toolbar {
    flex-direction: column;
    gap: 20px;
    padding: 20px;
  }
  
  .btn-new {
    width: 100%;
    justify-content: center;
    padding: 15px 20px;
  }
  
  .filter-section {
    width: 100%;
    justify-content: space-between;
  }
  
  .filter-select {
    flex: 1;
    min-width: auto;
  }
  
  .action-buttons {
    opacity: 1; /* Always visible on mobile */
  }
  
  .btn-action {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .project-name {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .project-icon {
    width: 35px;
    height: 35px;
    font-size: 14px;
  }
  
  .name-text {
    font-size: 14px;
  }
  
  .owner-avatar {
    width: 30px;
    height: 30px;
    font-size: 12px;
  }
}

/* Smooth Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-container {
  animation: fadeInUp 0.6s ease-out;
}

.toolbar {
  animation: fadeInUp 0.4s ease-out;
}

/* Focus States untuk Accessibility */
.btn-new:focus,
.btn-action:focus,
.filter-select:focus {
  outline: 2px solid #17677E;
  outline-offset: 2px;
}

/* Hover Effects untuk Interactive Elements */
.projects-table tbody tr {
  cursor: pointer;
}

.projects-table tbody tr:hover td {
  color: #17677E;
}

/* Custom Scrollbar untuk Table Container */
.table-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: rgba(23, 103, 126, 0.1);
  border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #17677E, #20c997);
  border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #145a6b, #1ea085);
}

/* Custom Modal untuk NETMAP Dashboard */
.netmap-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(23, 103, 126, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.netmap-modal {
  background: white;
  border-radius: 20px;
  box-shadow: 
    0 25px 60px rgba(23, 103, 126, 0.4),
    0 8px 25px rgba(0, 0, 0, 0.2);
  max-width: 450px;
  width: 90%;
  overflow: hidden;
  transform: scale(1);
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  border: 3px solid rgba(255, 255, 255, 0.1);
}

.netmap-modal-header {
  background: linear-gradient(135deg, #17677E 0%, #20c997 100%);
  color: white;
  padding: 25px 30px;
  position: relative;
  overflow: hidden;
}

.netmap-modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  text-align: center;
  letter-spacing: 0.5px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.netmap-modal-subtitle {
  font-size: 0.9rem;
  margin-top: 8px;
  opacity: 0.9;
  text-align: center;
  font-weight: 400;
}

.netmap-modal-body {
  padding: 35px 30px;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.netmap-input-group {
  margin-bottom: 25px;
}

.netmap-input-label {
  display: block;
  color: #17677E;
  font-weight: 600;
  margin-bottom: 10px;
  font-size: 1rem;
  letter-spacing: 0.3px;
}

.netmap-input {
  width: 100%;
  padding: 18px 20px;
  border: 2px solid #e9ecef;
  border-radius: 15px;
  font-size: 16px;
  font-family: inherit;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  box-sizing: border-box;
  background: white;
  color: #17677E;
  font-weight: 500;
}

.netmap-input:focus {
  outline: none;
  border-color: #17677E;
  box-shadow: 
    0 0 0 4px rgba(23, 103, 126, 0.15),
    0 8px 25px rgba(23, 103, 126, 0.2);
  transform: translateY(-2px);
  background: #fefefe;
}

.netmap-input.error {
  border-color: #dc3545;
  background: #fff5f5;
  box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15);
}

.netmap-error-message {
  color: #dc3545;
  font-size: 0.85rem;
  margin-top: 8px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.netmap-modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 30px;
}

.netmap-modal-btn {
  padding: 14px 28px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  font-family: inherit;
  transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
  min-width: 110px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.netmap-modal-btn-primary {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.netmap-modal-btn-primary:hover {
  background: linear-gradient(135deg, #218838, #1ea085);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 30px rgba(40, 167, 69, 0.5);
}

.netmap-modal-btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  color: white;
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

.netmap-modal-btn-secondary:hover {
  background: linear-gradient(135deg, #5a6268, #495057);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 30px rgba(108, 117, 125, 0.4);
}

.netmap-modal-btn.loading {
  pointer-events: none;
  opacity: 0.8;
}

.netmap-success-message {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  padding: 15px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 2px solid #b8dabc;
}

.sidebar,
.app-sidebar,
.left-sidebar,
.nav-sidebar {
  display: none !important;
  width: 0 !important;
  min-width: 0 !important;
  visibility: hidden !important;
}

.main {
  margin-left: 0 !important;
  width: 100% !important;
  padding-left: 0 !important;
}

/* Header should span full width (remove left offset) */
.header {
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
}

.dashboard-container {
  display: block !important;
  width: 100% !important;
}

h1 {
  margin-top: 20px !important;
}

@media (max-width: 1024px) {
  .main { margin-left: 0 !important; }
  .header { left: 0 !important; }
}

@media (max-width: 768px) {
  .main { margin-left: 0 !important; }
  .header { left: 0 !important; padding: 15px 20px; }
}

body, .app-root {
  padding-left: 0 !important;
}

</style>