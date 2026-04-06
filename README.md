# AralDeck Online 📚🚀

AralDeck Online is a modern, web-based flashcard and quiz platform designed for students and teachers. It provides a seamless learning experience through interactive study sets, real-time collaboration, and classroom management.

## ✨ Key Features

### 🔐 Authentication & Onboarding
* **Google Socialite Integration:** One-click login using Google accounts.
* **Intelligent Onboarding:** New users are automatically guided to choose their role (Student/Teacher) and a starting plan.
* **Real-time Validation:** Live AJAX-based email checking during registration to prevent duplicate accounts.

### 🃏 Flashcard Management
* **Rich Text Editor:** Create beautiful cards with CKEditor 5 supporting Base64 image uploads.
* **Bulk Upload:** Import entire decks via CSV/Excel spreadsheets.
* **Deck Settings:** Toggle between **Study Mode** (flippable cards) and **Timed Quiz Mode** (fill-in-the-blank).
* **Difficulty Scaling:** Adjustable timers for Easy, Average, and Hard cards.

### 👥 Collaboration & Classrooms
* **Shared Decks:** Invite collaborators via email to build study sets together.
* **Classroom Reports:** Teachers can assign decks to classrooms and track student performance with detailed grade reports and CSV exports.
* **Public Gallery:** Pro users can browse and clone community-made decks.

### 💳 Subscription System
* **Stacked Billing:** New plans stack on top of existing ones (new plans start exactly when the old one expires).
* **Trial Logic:** Automatic 1-Day Free Trial activation for new users (limited to one use).
* **Payment Integration:** Powered by PayMongo for secure transactions (Card, GCash, Maya).

## 🛠️ Tech Stack

* **Framework:** Laravel 11.x
* **Frontend:** Bootstrap 5, jQuery, DataTables
* **Database:** MySQL
* **Rich Text:** CKEditor 5
* **Auth:** Laravel UI & Socialite

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone [https://github.com/yourusername/araldeck.git](https://github.com/yourusername/araldeck.git)
   cd araldeck
