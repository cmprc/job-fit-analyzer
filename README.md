# Job Fit Analyzer

A full-stack web application that automatically analyzes and ranks candidate resumes against job descriptions using AI-powered analysis.

## Features

- Upload job descriptions and candidate resumes as PDF files
- AI-powered analysis using OpenAI GPT-3.5-turbo
- Automatic candidate ranking by fit score
- Detailed analysis with strengths and weaknesses
- Real-time analysis progress tracking

## Tech Stack

- **Backend**: Laravel 8.x (PHP 8.3+), MySQL 8.0
- **Frontend**: React 19, Vite, Tailwind CSS
- **AI**: OpenAI GPT-3.5-turbo API
- **Infrastructure**: Docker, Docker Compose

## Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/cmprc/job-fit-analyzer
   cd job-fit-analyzer
   ```

2. **Set OpenAI API Key**
   ```bash
   # Windows PowerShell:
   $env:OPENAI_API_KEY="your_openai_api_key_here"
   
   # Linux/Mac:
   export OPENAI_API_KEY=your_openai_api_key_here
   ```

3. **Start the application**
   ```bash
   docker-compose up --build -d
   ```

4. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000

## Usage

1. Upload a job description PDF
2. Upload candidate resume PDFs
3. Select the job and click "Run Analysis"
4. View candidate rankings and detailed analysis

## API Endpoints

- `GET /api/jobs` - List all jobs
- `POST /api/jobs` - Create new job
- `GET /api/candidates` - List all candidates
- `POST /api/candidates` - Create new candidate
- `GET /api/analyses?job_id={id}` - Get analyses for a job
- `POST /api/analyses/analyze-all` - Analyze all candidates for a job

## Database Credentials

- **Database**: job_fit_analyzer
- **Username**: job_fit_user
- **Password**: job_fit_password

## Troubleshooting

**OpenAI API Errors**: Verify API key is set correctly as environment variable
**PDF Upload Issues**: Ensure PDF contains extractable text (not just images)
**Database Issues**: Run `docker-compose exec backend php artisan migrate:fresh`

## Scoring & Explainability

The AI analysis uses OpenAI GPT-3.5-turbo to evaluate candidate-job fit through a structured scoring system:

### Scoring Methodology
- **Overall Fit Score**: 0-100 scale based on multiple criteria
- **Skills Match**: Technical skills alignment with job requirements
- **Experience Level**: Years of experience vs. job expectations
- **Education**: Educational background relevance
- **Industry Experience**: Domain-specific experience match

### Analysis Output
Each candidate receives:
- **Strengths**: Key qualifications that match the job
- **Weaknesses**: Areas where candidate falls short
- **Recommendations**: Suggestions for improvement
- **Detailed Breakdown**: Specific skill and experience analysis

### Explainability
The AI provides detailed explanations for each score component, allowing HR teams to understand the reasoning behind rankings and make informed hiring decisions.

## Trade-offs & Technical Decisions

### Technology Choices
- **Laravel Backend**: Chosen for rapid development, built-in API features, and robust ORM
- **React Frontend**: Selected for component reusability and modern development experience
- **MySQL Database**: Reliable relational database for structured job/candidate data
- **Docker Deployment**: Ensures consistent environments across development and production

### Parsing Library Decision
- **PDF Parsing**: Uses PHP's built-in PDF text extraction capabilities
- **Trade-off**: Limited to text-based PDFs, requires OCR for scanned documents
- **Alternative Considered**: Tesseract OCR integration (deferred due to complexity)

### Deployment Environment
- **Docker Compose**: Chosen for local development simplicity
- **Limitations**: Not optimized for production scaling
- **Production Considerations**: Would require Kubernetes or cloud container orchestration

### Current Limitations Accepted
- No user authentication (simplified for demo purposes)
- Synchronous processing (no background jobs for large batches)
- Basic PDF parsing (no OCR for scanned documents)
- Single-instance deployment (no horizontal scaling)

## What Would You Improve With More Time?

### Priority 1: Enhanced PDF Processing
- Implement OCR integration for scanned PDFs
- Support multiple file formats (DOCX, TXT)
- Better text extraction accuracy

### Priority 2: Scalability & Performance
- Background job processing for large candidate batches
- Horizontal scaling with load balancers
- Caching layer for repeated analyses
- Database optimization and indexing

### Priority 3: Advanced Features
- User authentication and role-based access
- Custom scoring criteria configuration
- Export analysis results to PDF/Excel
- Integration with ATS systems
- Advanced analytics and reporting dashboard

## Limitations

- Scanned PDFs require OCR (not implemented)
- No authentication system
- Analysis quality depends on PDF text quality
- No background job processing for large batches

---

*Built with enthusiasm and passion for solving real-world HR challenges! 🚀*
