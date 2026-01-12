# 📧 InboxPilot

> **AI-Powered Email Management System**  
> Automatically sync, categorize, and manage your Gmail inbox with artificial intelligence.

## 📖 Overview

**InboxPilot** is an intelligent email management application that revolutionizes how you handle your Gmail inbox. By leveraging the power of OpenAI's GPT models and Google's Gmail API, InboxPilot automatically syncs your emails, categorizes them using AI, generates summaries, and even detects unsubscribe links - all in real-time with background processing.

Say goodbye to email overload and let AI be your inbox assistant!

## ✨ Key Features

### 🔐 **Google OAuth Integration**
- Secure authentication with Google accounts
- OAuth 2.0 implementation with refresh token management
- Support for multiple Google accounts per user
- Automatic token refresh and validation

### 📨 **Gmail Synchronization**
- Real-time Gmail inbox synchronization
- Background job processing with queues
- Fetch emails with metadata (labels, threads, attachments)
- Automatic sync on user login
- Incremental sync based on last synced timestamp
- Support for unread, starred, and archived emails

### 🤖 **AI-Powered Email Processing**
- **Intelligent Categorization**: Automatically categorize emails based on content using GPT-3.5-turbo
- **Email Summaries**: Generate concise AI summaries of email content
- **Smart Unsubscribe Detection**: Identify unsubscribe links automatically
- **Context-Aware Analysis**: AI understands your custom categories and assigns emails accordingly

### 📁 **Custom Category Management**
- Create unlimited custom categories
- Add descriptions to improve AI categorization accuracy
- Configure auto-archive after processing
- Category-based email organization
- View emails grouped by categories

### 🔄 **Background Job Processing**
- Asynchronous email processing with Laravel Queues
- Three specialized jobs:
  - `SyncGmailEmailsJob`: Fetch and sync Gmail emails
  - `ProcessEmailJob`: Basic email processing
  - `ProcessEmailWithAIJob`: AI-powered analysis and categorization
- Configurable retry logic with exponential backoff
- Redis queue support for high performance
- Job monitoring with Laravel Horizon integration

### 📬 **Unsubscribe Management**
- Dedicated dashboard for emails with unsubscribe links
- Quick access to unsubscribe from unwanted newsletters
- AI-powered detection of unsubscribe URLs
- Filter and manage subscription emails easily

### 🔒 **Security Features**
- Laravel Fortify authentication
- Two-factor authentication support
- Sanctum API authentication
- Secure token storage and encryption
- Session management

### 🎨 **Modern User Interface**
- Clean and responsive dashboard
- Real-time status updates
- Email preview and details
- Category-based filtering
- Intuitive navigation

## 🛠️ Technology Stack

### **Backend**
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Authentication**: Laravel Fortify + Laravel Sanctum
- **API Integration**: Google API Client (Gmail API)
- **AI Integration**: OpenAI PHP SDK (GPT-3.5-turbo)
- **Queue System**: Redis with Predis
- **Database**: MySQL/PostgreSQL (configurable)
- **Testing**: PestPHP

### **Frontend**
- **CSS Framework**: Tailwind CSS 4.0
- **Build Tool**: Vite 7.0
- **JavaScript**: Axios for HTTP requests

### **Third-Party Services**
- **Google Cloud Platform**: Gmail API, OAuth 2.0
- **OpenAI**: GPT-3.5-turbo for email analysis
- **Redis**: Queue management and caching

### **Development Tools**
- Laravel Pint (Code style)
- Laravel Sail (Docker development environment)
- Laravel Tinker (REPL)
- Laravel Pail (Log viewing)
- Composer for dependency management
- NPM for frontend assets

## 📋 Prerequisites

Before installing InboxPilot, ensure you have:

- **PHP** 8.2 or higher
- **Composer** 2.x
- **Node.js** 18.x or higher with NPM
- **MySQL** 8.0+ or **PostgreSQL** 13+
- **Redis** server
- **Google Cloud Console** project with Gmail API enabled
- **OpenAI API** account with API key

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd InboxPilot
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables
Edit `.env` file with your credentials:

```env
# Application
APP_NAME=InboxPilot
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inboxpilot
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration
QUEUE_CONNECTION=redis

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# OpenAI
OPENAI_API_KEY=your_openai_api_key
```

### 5. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### 6. Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Queue Worker
```bash
# In a separate terminal
php artisan queue:work redis --tries=3
```

### 8. Start Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 🎯 Usage

### Getting Started

1. **Register/Login**: Create an account or login to InboxPilot
2. **Connect Gmail**: Click "Connect Google Account" and authorize Gmail access
3. **Create Categories**: Set up custom categories (e.g., "Work", "Personal", "Newsletters")
4. **Sync Emails**: Emails will automatically sync in the background
5. **AI Processing**: Watch as AI categorizes and summarizes your emails
6. **Manage Inbox**: View categorized emails and take actions

### Creating Categories

```php
// Categories help AI understand how to organize your emails
// Example categories:
- Name: "Work"
  Description: "Emails related to work projects, meetings, and colleagues"
  
- Name: "Newsletters"
  Description: "Marketing emails, promotional content, and subscriptions"
  
- Name: "Personal"
  Description: "Personal correspondence from friends and family"
```

### Queue Management

The application uses three main jobs:

1. **SyncGmailEmailsJob**: Fetches emails from Gmail
2. **ProcessEmailJob**: Processes basic email data
3. **ProcessEmailWithAIJob**: Performs AI analysis

Monitor queue status:
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## 🏗️ Architecture

### Directory Structure
```
app/
├── Http/Controllers/      # Web controllers
├── Jobs/                  # Queue jobs
├── Models/                # Eloquent models
├── Services/              # Business logic
│   ├── GmailService.php   # Gmail API integration
│   ├── OpenAIService.php  # AI processing
│   └── TextFormatter.php  # Email formatting
└── Helpers/               # Helper functions

database/
├── migrations/            # Database schema
└── seeders/              # Data seeders

resources/
├── views/                # Blade templates
├── css/                  # Stylesheets
└── js/                   # JavaScript files

routes/
├── web.php               # Web routes
└── api.php               # API routes
```

### Database Schema

**Users** → Stores user accounts  
**Google Accounts** → OAuth credentials for Gmail  
**Categories** → User-defined email categories  
**Emails** → Synced Gmail messages with AI data  
**Jobs** → Queue job tracking  

## 🔧 Configuration

### Queue Configuration
Edit `config/queue.php` for queue settings:
- Connection: Redis
- Retry after: 90 seconds
- Max attempts: 3

### OpenAI Configuration
Edit `config/openai.php`:
- Model: GPT-3.5-turbo
- Temperature: 0.0 (deterministic)
- Max tokens: Configurable

### Gmail Sync Settings
- Max results per sync: Configurable
- Auto-sync on login: Enabled by default
- Incremental sync: Based on timestamps

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## 🐛 Troubleshooting

See [QUEUE_TROUBLESHOOTING.md](QUEUE_TROUBLESHOOTING.md) for detailed queue debugging steps.

### Common Issues

**Queue not processing**:
```bash
php artisan queue:restart
php artisan queue:work redis
```

**Token refresh errors**: Re-authenticate with Google

**OpenAI rate limits**: Check API usage and upgrade plan

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- Laravel Framework
- Google Gmail API
- OpenAI API
- Tailwind CSS
- The open-source community

---

**Built with ❤️ using Laravel and AI**
