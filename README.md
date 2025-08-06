<p align="center"><a href="#logo"><img src="https://raw.githubusercontent.com/mailmug/laravel-easy-pos/main/easy-pos-logo.svg" width="400" alt="Laravel POS Logo"></a></p>

A simple, open-source **Point of Sale (POS) system** built with Laravel and FilamentPHP.


## Features 🛠️
- ✅ Easy-to-use POS interface
- ✅ Built with Laravel & FilamentPHP
- ✅ Secure authentication & user management
- ✅ Inventory & product management
- ✅ Sales tracking & reports
- ✅ Responsive UI


## 🚀 Demo Available:

**Live Demo** : https://filament-pos.phpbolt.com/

**Username:** admin@admin.com

**Password:** pass@123
<br>

## ⭐ Show your support!
If you find this project helpful, please consider giving it a star on GitHub

<br>

## Installation & Custom Invoice Template Service

We offer a hassle-free installation service for Laravel Easy POS for just **$29**. 
Need a custom invoice template? We can design one for an additional price!

✅ **Services Offered:**

- **Installation & Setup** – $29
- **Custom Invoice Template** – Additional cost (contact us for pricing)
- Configuration assistance & basic troubleshooting

* 📩 Contact Us:
* ✉️ Email: info@mailmug.net
* 💬 Discord: arshidkv12
* 🌐 Website: [wpdebuglog.com/contact/](https://wpdebuglog.com/contact/)

Get in touch today, and let us handle the setup for you! 🚀


### **POS Interface**  
![POS Interface](https://raw.githubusercontent.com/mailmug/laravel-easy-pos/main/public/img/laravel-easy-pos.png)  


### **Invoice**  

It supports thermal printing.

<p align="center">
  <img src="https://raw.githubusercontent.com/mailmug/laravel-easy-pos/main/public/img/invoice.png" alt="POS Invoice" style="border:1px solid #ddd">
</p> 


## Installation Guide 🏗️

### Web Install
1. Download the zip file 
https://filament-pos.phpbolt.com/laravel-easy-pos.zip

2. Upload the file to **public_html** folder.

Point the domain name to public_html/public folder.

3. Navigate your-domain.com/install


### Local Install

1. **Clone the repository:**

```shell
git clone https://github.com/mailmug/laravel-easy-pos.git
cd laravel-easy-pos

```
2. **Copy .env file**

```shell
cp .env.example .env
php artisan key:generate

```

3. **Update .env file**

```shell
DB_CONNECTION=mysql
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
APP_URL=http://localhost

```

4. **Install dependencies:**

```shell
composer install
npm install
npm run build
```

5. **Run the application:**

```shell
php artisan serve
```

Navigate to the home page, and it will automatically add the demo data.

**Username:** admin@admin.com

**Password:** pass@123


✅ That's it! No further commands needed. The installation is automatic. 🎉 
Navigate the website. 


## Contribute 🤝
We ❤️ contributions! Feel free to submit issues or pull requests.

1. Fork the repo
2. Create a new branch
3. Commit your changes
4. Open a Pull Request
 

## License 📜
This project is licensed under the GPL-3.0 License.


**💡 Built with Laravel & FilamentPHP – Making POS Simple! 🚀**

Let me know if you want to add anything specific! 😃
