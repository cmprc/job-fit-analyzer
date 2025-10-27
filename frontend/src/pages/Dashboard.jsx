import React, { useState, useEffect } from 'react';
import FileUpload from '../components/FileUpload';
import CandidateRankingTable from '../components/CandidateRankingTable';
import CandidateDetailsModal from '../components/CandidateDetailsModal';
import Loader from '../components/Loader';
import apiService from '../services/api';

const Dashboard = () => {
  const [jobs, setJobs] = useState([]);
  const [candidates, setCandidates] = useState([]);
  const [analyses, setAnalyses] = useState([]);
  const [selectedJob, setSelectedJob] = useState(null);
  const [selectedAnalysis, setSelectedAnalysis] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setInitialLoading(true);
      const [jobsData, candidatesData] = await Promise.all([
        apiService.getJobs(),
        apiService.getCandidates()
      ]);
      setJobs(jobsData);
      setCandidates(candidatesData);
      
      // If there's only one job and candidates exist, auto-select and analyze
      if (jobsData.length === 1 && candidatesData.length > 0) {
        const job = jobsData[0];
        setSelectedJob(job);
        await handleAnalyzeAll(job.id);
      }
    } catch (error) {
      console.error('Failed to load data:', error);
    } finally {
      setInitialLoading(false);
    }
  };

  const handleJobUpload = async (file) => {
    const formData = new FormData();
    formData.append('pdf', file);
    formData.append('title', file.name.replace('.pdf', ''));
    
    try {
      const newJob = await apiService.createJob(formData);
      setJobs([...jobs, newJob]);
      setSelectedJob(newJob);
      
      // Automatically trigger analysis if candidates are available
      if (candidates.length > 0) {
        await handleAnalyzeAll(newJob.id);
      }
    } catch (error) {
      throw error;
    }
  };

  const handleCandidateUpload = async (file) => {
    const formData = new FormData();
    formData.append('pdf', file);
    formData.append('name', file.name.replace('.pdf', ''));
    
    try {
      const newCandidate = await apiService.createCandidate(formData);
      setCandidates([...candidates, newCandidate]);
    } catch (error) {
      throw error;
    }
  };

  const handleAnalyzeAll = async (jobId = null) => {
    const jobToAnalyze = jobId ? jobs.find(j => j.id === jobId) : selectedJob;
    
    if (!jobToAnalyze) {
      alert('Please select a job first');
      return;
    }

    if (candidates.length === 0) {
      alert('No candidates available for analysis');
      return;
    }

    setLoading(true);
    try {
      const response = await apiService.analyzeAllCandidates(jobToAnalyze.id);
      
      // Handle both old format (array) and new format (object with analyses and summary)
      const analysesData = Array.isArray(response) ? response : response.analyses;
      setAnalyses(analysesData);
      
      // Show summary if available
      if (response.summary) {
        console.log(`Analysis completed: ${response.summary.new_analyses} new analyses, ${response.summary.existing_analyses} existing analyses`);
      }
    } catch (error) {
      alert('Analysis failed: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleViewDetails = (analysis) => {
    setSelectedAnalysis(analysis);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setSelectedAnalysis(null);
  };

  return (
    <div className="min-h-screen bg-gray-100">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Job Fit Analyzer</h1>
          <p className="mt-2 text-gray-600">
            Upload job descriptions and resumes to automatically analyze candidate fit
          </p>
        </div>

        {initialLoading ? (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Job Descriptions</h2>
              <Loader text="Loading job descriptions..." className="py-8" />
            </div>
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Candidates</h2>
              <Loader text="Loading candidates..." className="py-8" />
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {/* Job Upload */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Upload Job Description</h2>
              <FileUpload
                onUpload={handleJobUpload}
                label="Upload Job Description PDF"
              />
              
              {jobs.length > 0 && (
                <div className="mt-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Select Job for Analysis:
                  </label>
                  <select
                    value={selectedJob?.id || ''}
                    onChange={async (e) => {
                      const jobId = parseInt(e.target.value);
                      const job = jobs.find(j => j.id === jobId);
                      setSelectedJob(job);
                      setAnalyses([]);
                      
                      // Automatically trigger analysis if candidates are available
                      if (job && candidates.length > 0) {
                        await handleAnalyzeAll(jobId);
                      }
                    }}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2"
                  >
                    <option value="">Select a job...</option>
                    {jobs.map(job => (
                      <option key={job.id} value={job.id}>
                        {job.title}
                      </option>
                    ))}
                  </select>
                </div>
              )}
            </div>

            {/* Candidate Upload */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">Upload Candidate Resume</h2>
              <FileUpload
                onUpload={handleCandidateUpload}
                label="Upload Resume PDF"
              />
              
              {candidates.length > 0 && (
                <div className="mt-4">
                  <p className="text-sm text-gray-600">
                    Uploaded candidates: <span className="font-medium">{candidates.length}</span>
                  </p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Analysis Section */}
        {selectedJob && candidates.length > 0 && (
          <div className="bg-white rounded-lg shadow p-6 mb-8">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold text-gray-900">
                Analysis for: {selectedJob.title}
              </h2>
              {loading && (
                <div className="flex items-center text-blue-600">
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                  Analyzing {candidates.length} candidates...
                </div>
              )}
            </div>
            
            <p className="text-sm text-gray-600 mb-4">
              {loading 
                ? `Analyzing all ${candidates.length} candidates against the selected job description using AI...`
                : `Analysis completed for ${candidates.length} candidates against "${selectedJob.title}"`
              }
            </p>
            
            {!loading && analyses.length === 0 && (
              <div className="text-center py-4 text-gray-500">
                <p>Analysis will start automatically when you select a job.</p>
              </div>
            )}
          </div>
        )}

        {/* Results */}
        {analyses.length > 0 && (
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-4">Candidate Rankings</h2>
            <CandidateRankingTable
              analyses={analyses}
              onViewDetails={handleViewDetails}
            />
          </div>
        )}

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-blue-600">
              {initialLoading ? <Loader size="sm" text="" /> : jobs.length}
            </div>
            <div className="text-sm text-gray-600">Job Descriptions</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-green-600">
              {initialLoading ? <Loader size="sm" text="" /> : candidates.length}
            </div>
            <div className="text-sm text-gray-600">Candidates</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-2xl font-bold text-purple-600">
              {initialLoading ? <Loader size="sm" text="" /> : analyses.length}
            </div>
            <div className="text-sm text-gray-600">Analyses Completed</div>
          </div>
        </div>
      </div>

      <CandidateDetailsModal
        analysis={selectedAnalysis}
        isOpen={isModalOpen}
        onClose={handleCloseModal}
      />
    </div>
  );
};

export default Dashboard;
