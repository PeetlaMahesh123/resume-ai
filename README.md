

# Resume AI - Getting Started Guide

## 🚀 Quick Setup (5 minutes)
1. Visit: https://platform.openai.com/api-keys
2. Click "Create new secret key"
3. Copy the key
4. Open `.env.php` file in the `resume-ai` folder
5. Replace `'your-api-key-here'` with your actual key

### 1. **Move folder to XAMPP**
Move the `resume-ai` folder to: `C:\xampp\htdocs\resume-ai`

### 2. **Create Database**
- Open: http://localhost/phpmyadmin
- Click "New" on left
- Database name: `resume_ai`
- Click "Create"
- Go to "Import" tab
- Upload `database.sql` file
- Click "Go"

✅ Database created!

---
## 🚀 Final Result
<img width="1906" height="871" alt="image" src="https://github.com/user-attachments/assets/f9880636-3deb-4e3f-a509-f00c89e8cdd3" />

---

### 3. **Get OpenAI API Key**

**Important**: Never share this key!

---

### 4. **Start XAMPP**
- Start Apache and MySQL in XAMPP Control Panel

---

### 5. **Access the App**
Open in your browser: **http://localhost/resume-ai**

---

## 📝 User Flow

### First Time User:
1. Click "Get Started Free" on homepage
2. Register (email + password)
3. Click "Create New Resume"
4. Fill in your details
5. Click "Generate Resume with AI"
6. See preview with ATS score
7. Download PDF or ask AI to improve sections

---

## 🔑 Key Features Working



---

## 📊 File Structure
```
resume-ai/
├── index.html              # Homepage
├── register.html           # Registration
├── login.html              # Login
├── resume-form.html        # Form to enter resume
├── dashboard.php           # User dashboard
├── preview.php             # Show improved resume
├── .env.php                # API Key (keep secret!)
├── db.php                  # Database connection
├── database.sql            # SQL setup
│
├── api/
│   ├── register.php        # Handle registration
│   ├── login.php           # Handle login
│   ├── logout.php          # Handle logout
│   ├── ai_resume.php       # Main AI logic
│   └── chat_ai.php         # Chat functionality
│
├── pdf/
│   └── generate.php        # PDF generation
│
├── css/
│   └── style.css           # All styling
│
└── js/
    └── main.js             # Frontend logic
```

---

## 🛠️ Troubleshooting

### "Database connection failed"
- Make sure MySQL is running in XAMPP
- Check username is `root` and password is empty
- Check database name is `resume_ai`

### "API Error" or blank response
- Check your OpenAI API key in `.env.php`
- Make sure you have API credits (https://platform.openai.com/account/billing/overview)
- Check if API key is valid

### "Page not found"
- Make sure folder is in `C:\xampp\htdocs\resume-ai`
- Access via `http://localhost/resume-ai` (not `http://localhost/res`)

---

## 🚀 Next Steps to Upgrade

1. **Better PDF Generation** - Install dompdf for professional PDFs
2. **Job Description Matching** - Upload job description, AI matches your resume
3. **Multiple Templates** - Choose from resume templates
4. **Resume History** - View all your past resumes
5. **Payment Gateway** - Add premium features

---

## 💡 Tips

- Test with a sample resume first
- Try asking AI: "Make my experience section more impressive"
- Download and check the PDF output
- Try different job roles to see how AI adapts

---

Need help? Check the code comments in each PHP file!
