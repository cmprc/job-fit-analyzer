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
   git clone <repository-url>
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

- **Host**: localhost:3306
- **Database**: job_fit_analyzer
- **Username**: job_fit_user
- **Password**: job_fit_password

## Troubleshooting

**OpenAI API Errors**: Verify API key is set correctly as environment variable
**PDF Upload Issues**: Ensure PDF contains extractable text (not just images)
**Database Issues**: Run `docker-compose exec backend php artisan migrate:fresh`

## Production Deployment

Set environment variables in `.env`:
```bash
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
OPENAI_API_KEY=your_openai_key
```

## Limitations

- Scanned PDFs require OCR (not implemented)
- No authentication system
- Analysis quality depends on PDF text quality
- No background job processing for large batches