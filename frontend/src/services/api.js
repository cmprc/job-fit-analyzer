const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

class ApiService {
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || `HTTP error! status: ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  async getJobs() {
    return this.request('/jobs');
  }

  async createJob(formData) {
    return this.request('/jobs', {
      method: 'POST',
      body: formData,
      headers: {},
    });
  }

  async getJob(id) {
    return this.request(`/jobs/${id}`);
  }

  async updateJob(id, data) {
    return this.request(`/jobs/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteJob(id) {
    return this.request(`/jobs/${id}`, {
      method: 'DELETE',
    });
  }

  async getCandidates() {
    return this.request('/candidates');
  }

  async createCandidate(formData) {
    return this.request('/candidates', {
      method: 'POST',
      body: formData,
      headers: {},
    });
  }

  async getCandidate(id) {
    return this.request(`/candidates/${id}`);
  }

  async updateCandidate(id, data) {
    return this.request(`/candidates/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteCandidate(id) {
    return this.request(`/candidates/${id}`, {
      method: 'DELETE',
    });
  }

  async getAnalyses(jobId) {
    return this.request(`/analyses?job_id=${jobId}`);
  }

  async createAnalysis(data) {
    return this.request('/analyses', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async analyzeAllCandidates(jobId) {
    return this.request('/analyses/analyze-all', {
      method: 'POST',
      body: JSON.stringify({ job_id: jobId }),
    });
  }

  async getAnalysis(id) {
    return this.request(`/analyses/${id}`);
  }

  async deleteAnalysis(id) {
    return this.request(`/analyses/${id}`, {
      method: 'DELETE',
    });
  }
}

export default new ApiService();
