# Quran App - Al-Quran Experience

Welcome to the **Al-Quran App**! This app is designed to provide Muslims with a comprehensive, modern, and feature-rich experience for their daily religious practices. Whether you are looking to pray on time, read the Quran, learn Tajweed, or receive AI-powered recitation feedback, this app has it all!

## Features

### Free Plan Features
- **Prayer Times (Salat Times)**: Get accurate prayer times based on your location.
- **Quran (with Translation & Tafsir)**: Access the Quran in Arabic with translation in multiple languages.
- **Qibla Direction**: Find the accurate Qibla direction using GPS-based location services.
- **Multiple Quran Fonts**: Choose from various font styles to enhance readability.
- **Multiple Language Support**: Access the app in a variety of languages including English, Arabic, Urdu, Bengali, Turkish, and more.

### Basic Plan Features
- All features from the **Free Plan**, plus:
- **Tajweed Error Detection and Correction**: Detect and correct Tajweed errors during Quran recitation.
- **AI-Powered Quran Recitation Feedback**: Receive real-time AI feedback on your recitation.
- **Quran Text + Audio Recitation**: Read and listen to the Quran with synchronized audio recitations from multiple Qari (reciters).
- **Multiple Recitation Styles**: Select from renowned Qaris (reciters) such as Al-Shuraym, Al-Minshawi, and more.

### Premium Plan Features
- All features from the **Basic Plan**, plus:
- **Ad-Free Experience**: Enjoy an uninterrupted, ad-free experience while using the app.

## Installation

Follow the steps below to get the app running on your local machine.

### Prerequisites

1. PHP >= 8.2
2. Composer
3. MySQL or any other supported database

### Steps to Install:

1. Clone the repository:
    ```bash
    git clone
    cd file path
    ```

2. Install dependencies:
    ```bash
    composer install && npm install
    ```

3. Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```

4. Generate an application key:
    ```bash
    php artisan key:generate
    ```

5. Configure your database settings in the `.env` file.

6. Run migrations to set up the database schema:
    ```bash
    php artisan migrate
    ```

7. Seed the database with the initial data (including feature and package seeder):
    ```bash
    php artisan db:seed
    ```

8. Start the development server:
    ```bash
    npm run build
    ```

9. Start the development server:
    ```bash
    php artisan serve
    ```

10. Visit your application in the browser:
    ```
    http://127.0.0.1:8000
    ```

## How the App Works

- **Prayer Times**: The app calculates prayer times based on your location using GPS and offers customizable notifications.
- **Quran Access**: You can read the Quran in Arabic with translations in multiple languages, and access Tafsir for in-depth understanding.
- **Qibla Direction**: The app uses your device's GPS to show you the exact direction to face for prayer.
- **Recitation Features**: The app provides audio recitations with multiple reciters and the ability to choose different Tajweed styles.
- **AI Recitation Feedback**: The app provides AI-driven feedback on your Quran recitation to help improve your pronunciation and Tajweed.
  
## Packages

There are three different subscription packages available:

1. **Free Plan**: Includes essential features like prayer times, Quran access, Qibla direction, and multiple language support.
2. **Basic Plan**: Includes all the features of the Free Plan, plus Tajweed error detection, audio recitations, AI recitation feedback, and more.
3. **Premium Plan**: Includes all features from the Basic Plan, plus an ad-free experience.

## Technologies Used

- **PHP**: Backend framework using Laravel.
- **MySQL**: Database management system for storing app data.
- **Laravel**: PHP framework for building web applications.
- **Vue.js**: JavaScript framework for building interactive front-end components.
- **Tailwind CSS**: Utility-first CSS framework for designing responsive user interfaces.
- **AI Models**: Used for Quran recitation feedback and error detection.

## Contributing

We welcome contributions to improve the app! If you have an idea for a feature or find a bug, feel free to fork the repository, make your changes, and submit a pull request.

### Steps for Contributing:
1. Fork the repository.
2. Create a new branch for your feature or fix.
3. Make your changes and commit them.
4. Push your branch and open a pull request.

## License

This app is open-source and licensed under the MIT License.

## Contact

If you have any questions or feedback, feel free to reach out via:
- Email: husoaib422@gmail.com
- Website: https://devhelal.com

---

Thank you for using **Al-Quran App**. May it help you in your journey of faith!
