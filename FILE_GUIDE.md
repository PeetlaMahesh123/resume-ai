# 📋 Resume AI - File Structure & Purpose Guide

## 🎯 PROJECT OVERVIEW

Your complete Resume AI application with AI-powered resume optimization powered by ChatGPT.

**Location**: `C:\Users\Windows 11\OneDrive\Desktop\res\resume-ai\`

---

## 📁 FILE STRUCTURE & WHAT EACH FILE DOES

```
resume-ai/
│
├── 🏠 PUBLIC PAGES (User-facing)
│
├── index.html
│   Purpose: Landing page showing features
│   What it shows: Hero section, features, CTA buttons
│   Users see: Homepage on first visit
│
├── register.html
│   Purpose: User registration page
│   What it does: Collects name, email, password
│   Backend: Calls api/register.php
│
├── login.html
│   Purpose: User login page
│   What it does: Email & password login form
│   Backend: Calls api/login.php
│
├── resume-form.html
│   Purpose: Resume input form (6 sections)
│   What it does: Collects personal info, summary, skills, experience, projects, education
│   Backend: Calls api/ai_resume.php
│
├── dashboard.php
│   Purpose: User dashboard (PHP - requires login)
│   What it shows: All user's past resumes with dates & ATS scores
│   Features: View, Download, Create New buttons
│
├── preview.php ⭐ IMPORTANT
│   Purpose: Shows AI-improved resume & chat
│   What it shows:
│     - Original resume entered by user
│     - AI-improved version (from ChatGPT)
│     - ATS Score (0-100)
│     - AI Chat Assistant
│   Features: Download PDF, Ask AI to improve sections
│
│
├── 🔧 BACKEND API FILES (do the work)
│
├── api/
│
│   ├── register.php
│   │   Purpose: Handle user registration
│   │   Input: name, email, password
│   │   Output: Creates user, starts session
│   │   Database: Inserts into users table
│   │
│   ├── login.php
│   │   Purpose: Handle user login
│   │   Input: email, password
│   │   Output: Validates password, starts session
│   │   Database: Queries users table
│   │
│   ├── logout.php
│   │   Purpose: Clear user session
│   │   Input: None
│   │   Output: Destroys session, redirects home
│   │
│   ├── ai_resume.php ❤️ CORE FILE
│   │   Purpose: Main AI magic happens here!
│   │   Input: resume form data (name, skills, experience, etc.)
│   │   Process:
│   │     1. Build prompt for ChatGPT
│   │     2. Call OpenAI API (requires .env.php API key)
│   │     3. Get improved resume from AI
│   │     4. Calculate ATS Score (0-100)
│   │     5. Save everything to database
│   │   Output: Redirects to preview.php to show result
│   │   Database: Inserts into resumes table
│   │   Dependencies: .env.php for API key
│   │
│   └── chat_ai.php
│       Purpose: Handle user questions about resume
│       Input: resume_id, user question
│       Process:
│         1. Get resume from database
│         2. Build prompt with resume + question
│         3. Call OpenAI API
│         4. Return AI response
│       Output: JSON response to frontend
│       Dependencies: .env.php for API key
│
│
├── ⚙️ CONFIGURATION FILES
│
├── .env.php
│   Purpose: Store OpenAI API Key
│   Content: define('OPENAI_API_KEY', 'your-key-here');
│   Important: Keep this secret! Never commit to GitHub
│   Setup: Updated via setup.php
│
├── db.php
│   Purpose: Database connection setup
│   Content:
│     - MySQL credentials (localhost, root, "")
│     - Database name (resume_ai)
│     - Session start
│   Used: Included in every PHP file
│
├── database.sql
│   Purpose: SQL schema (for manual setup only)
│   Content: CREATE TABLE statements for users & resumes
│   Status: Already imported to MySQL (you don't need to run this)
│
│
├── 🎨 FRONTEND FILES
│
├── css/
│   └── style.css
│       Purpose: All styling for the app
│       Content:
│         - Navbar styling
│         - Button styles
│         - Form styling
│         - Responsive design
│         - Mobile responsive (@media queries)
│
├── js/
│   └── main.js
│       Purpose: Frontend JavaScript
│       Content:
│         - Form validation
│         - Loading states
│         - User feedback
│
│
├── 📄 PDF EXPORT
│
├── pdf/
│   └── generate.php
│       Purpose: Generate and download resume as PDF
│       Input: resume_id (from URL parameter)
│       Process: Formats resume, outputs as PDF file
│       Note: Basic HTML-to-PDF (upgrade path: install dompdf)
│
│
├── 📚 DOCUMENTATION & SETUP
│
├── setup.php ⭐ START HERE!
│   Purpose: Interactive setup verification & API key configuration
│   What it does:
│     - Checks database connection
│     - Verifies all files exist
│     - Shows API key configuration form
│     - Displays completion progress
│   Access: http://localhost:8000/setup.php
│
├── STATUS.txt
│   Purpose: Quick status reference (this file)
│   Content: Current setup status, links, instructions
│
├── QUICK_START.md
│   Purpose: 5-minute quick start guide
│   Content: Setup steps, test flow, troubleshooting
│
├── README.md
│   Purpose: Complete documentation
│   Content: Full setup guide, features, upgrades, etc.
│
├── FILE_GUIDE.md (this file)
│   Purpose: Explain what each file does
│   You are reading this!
│
└── 🚀 LAUNCHER SCRIPTS
    ├── START.bat
    │   Purpose: Quick launcher for Windows
    │   What it does: Starts PHP server, opens setup page
    │   How to use: Double-click in File Explorer
    │
    └── start.sh
        Purpose: Launcher for Linux/Mac
        What it does: Starts PHP server, opens setup page
        How to use: bash start.sh
```

---

## 🔄 HOW THE APP WORKS - USER FLOW

```
1️⃣  User visits http://localhost:8000
    ↓
2️⃣  Homepage loads (index.html)
    ↓
3️⃣  Clicks "Get Started Free"
    ↓
4️⃣  Registration page loads (register.html)
    ↓
5️⃣  Fills email/password, submits
    ↓
6️⃣  api/register.php:
    - Hashes password
    - Inserts into users table
    - Starts session
    - Redirects to dashboard
    ↓
7️⃣  Dashboard loads (dashboard.php)
    - Shows user's past resumes
    ↓
8️⃣  Clicks "Create New Resume"
    ↓
9️⃣  Resume form loads (resume-form.html)
    ↓
🔟  Fills resume details, selects job role, submits
    ↓
1️⃣1️⃣  api/ai_resume.php:
      - Gets form data
      - Creates ChatGPT prompt
      - Calls OpenAI API
      - Gets improved resume
      - Calculates ATS score
      - Saves to database
      - Redirects to preview
    ↓
1️⃣2️⃣  Preview loads (preview.php)
      - Shows original input
      - Shows AI-improved version
      - Shows ATS Score (0-100)
      - Shows chat input box
    ↓
1️⃣3️⃣  User asks AI for improvement
    ↓
1️⃣4️⃣  api/chat_ai.php:
      - Gets resume + question
      - Calls OpenAI API
      - Returns helpful response
    ↓
1️⃣5️⃣  User downloads PDF
    ↓
1️⃣6️⃣  pdf/generate.php:
      - Gets resume from database
      - Formats as PDF
      - Downloads to user's computer
    ↓
1️⃣7️⃣  Done! 🎉
```

---

## 💾 DATABASE STRUCTURE

### users table
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),  -- hashed
  created_at TIMESTAMP
);
```

### resumes table
```sql
CREATE TABLE resumes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,           -- links to users table
  resume_data LONGTEXT,  -- original JSON data
  ai_resume LONGTEXT,    -- AI-improved resume
  ats_score INT,         -- 0-100 score
  created_at TIMESTAMP
);
```

---

## 🔑 KEY FEATURES BY FILE

| Feature | Primary File | Dependency |
|---------|--------------|------------|
| Display Homepage | index.html | css/style.css |
| User Registration | register.html | api/register.php, db.php |
| User Login | login.html | api/login.php, db.php |
| Resume Input | resume-form.html | api/ai_resume.php, js/main.js |
| AI Resume Improvement | api/ai_resume.php | .env.php (API key), ChatGPT |
| Show Preview | preview.php | db.php |
| AI Chat | api/chat_ai.php | .env.php (API key), ChatGPT |
| PDF Download | pdf/generate.php | db.php |
| Dashboard | dashboard.php | db.php |
| Setup Verification | setup.php | db.php, .env.php |

---

## ⚡ API ENDPOINTS SUMMARY

```
POST /api/register.php
  Input: name, email, password
  Output: Creates user, session

POST /api/login.php
  Input: email, password
  Output: Session, redirect

GET /api/logout.php
  Input: None
  Output: Destroys session

POST /api/ai_resume.php
  Input: resume form fields
  Output: Saves resume, redirects

POST /api/chat_ai.php
  Input: resume_id, question
  Output: JSON response

GET /pdf/generate.php?id=X
  Input: resume_id
  Output: PDF file download
```

---

## 🚦 WHICH FILE TO EDIT FOR WHAT

**Want to change the look?**
→ Edit `css/style.css`

**Want to change the form fields?**
→ Edit `resume-form.html` and `api/ai_resume.php`

**Want to change AI prompt behavior?**
→ Edit the `$prompt` variable in `api/ai_resume.php`

**Want to change ATS scoring logic?**
→ Edit the `calculateATSScore()` function in `api/ai_resume.php`

**Want to change database structure?**
→ Edit `db.php` connection & `database.sql` schema (then reimport)

**Want to add new pages?**
→ Create new `.html` file (frontend) or `.php` file (backend)

**Want to change colors?**
→ Edit color values in `css/style.css`

**Want to change button text?**
→ Search & replace in `.html` or `.php` files

---

## 📊 FILE STATISTICS

- **Total Files**: 28
- **Frontend Files**: 6 (HTML/CSS/JS)
- **Backend Files**: 9 (PHP APIs)
- **Configuration Files**: 3 (db.php, .env.php, database.sql)
- **Documentation Files**: 5 (README.md, QUICK_START.md, etc.)
- **Directories**: 4 (api, css, js, pdf)

---

## ✅ VERIFICATION CHECKLIST

After setup, verify:

- [ ] PHP server running on localhost:8000
- [ ] MySQL database `resume_ai` created
- [ ] Tables `users` and `resumes` exist
- [ ] All 28 files present
- [ ] `.env.php` has OpenAI API key
- [ ] Can access http://localhost:8000/setup.php
- [ ] setup.php shows all green checkmarks
- [ ] Can register new account
- [ ] Can create resume and see AI improvement
- [ ] ATS Score displays (0-100)
- [ ] AI Chat works

---

## 🎓 NEXT STEPS TO ENHANCE

**Easy (1-2 hours)**:
- [ ] Add more resume sections
- [ ] Improve CSS styling & colors
- [ ] Add form validation
- [ ] Better error messages

**Medium (2-4 hours)**:
- [ ] Install dompdf for professional PDFs
- [ ] Add resume templates (multiple styles)
- [ ] Add email export feature
- [ ] Search/filter resume history

**Advanced (4+ hours)**:
- [ ] Job description matching
- [ ] Multiple language support
- [ ] Authentication with OAuth
- [ ] Payment integration (Stripe)
- [ ] Email notifications
- [ ] Analytics dashboard

---

**Created**: February 8, 2026  
**Version**: 1.0 MVP  
**Status**: ✅ Fully Automated Setup Complete
