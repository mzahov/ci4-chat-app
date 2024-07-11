# CodeIgniter 4 Chat Application

## Introduction

This is a real-time chat application built with Codeigniter4, jQuery, Bootstrap5, Vite, and Rachet WebSocket. The application supports both individual and group chats, and includes RESTful CRUD operations for managing chats.

## Features

- Real-time communication using WebSockets
- Individual and group chats
- RESTful CRUD operations for chats
- User authentication and authorization
- Responsive design with Bootstrap5
- Efficient asset management using Vite

## Technologies Used

- **Backend:** Codeigniter4
- **Frontend:** jQuery, Bootstrap5
- **Build Tool:** Vite
- **WebSocket:** Rachet WebSocket

## Installation

Follow these steps to set up the project locally:

### Prerequisites
- PHP 7.4 or higher
- Composer
- Node.js and npm

## Steps

1. Clone the repository
```
git clone https://github.com/mzahov/ci4-chat-app.git
```

2. Install PHP dependencies
```
composer install
```

3. Install Node.js dependencies
```
npm install
```

5. Set up environment variables
```
cp env .env
```

5. Edit environment variables for your app, specifically the baseURL, database settings, Vite and WebSocket Port.
```
app.baseURL = ''

database.default.hostname = localhost
database.default.database = ci4
database.default.username = root
database.default.password = root
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306


VITE_SERVE = true
VITE_HOST = 'localhost'
VITE_PORT = 5173

WS_PORT = 8080
```

6. Set Secret Key in .env
- Generate at least 256 bits random string
```
php -r 'echo base64_encode(random_bytes(32));'
```

- Add it in your .env file
```
authjwt.keys.default.0.secret = ''
```

7. Run database migrations
```
php spark migrate
```

8. Start the development server
```
php spark serve
```

9. Start the vite server
```
npm run dev
```

10. Start the WebSocket server
```
php spark websocket:start
```

## Screenshots

<p float="left">
  <img src="https://github.com/mzahov/ci4-chat-app/blob/main/screenshots/chat-list-mobile.jpeg" width=49%>
  <img src="https://github.com/mzahov/ci4-chat-app/blob/main/screenshots/chat-open-room-mobile.jpeg" width=49%>
</p>

<p float="left">
  <img src="https://github.com/mzahov/ci4-chat-app/blob/main/screenshots/chat-empty.png" width=49%>
  <img src="https://github.com/mzahov/ci4-chat-app/blob/main/screenshots/chat-open-room.png" width=49%>
  <img src="https://github.com/mzahov/ci4-chat-app/blob/main/screenshots/chat-login.png" width=49%>
</p>
