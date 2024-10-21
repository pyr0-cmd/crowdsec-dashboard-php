<!-- login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crowdsec Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h2>
        <form action="process_login.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-200" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-200" required>
            </div>
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center">
                    <input type="checkbox" class="form-checkbox text-blue-600">
                    <span class="ml-2 text-gray-700">Remember me</span>
                </label>
                <a href="#" class="text-blue-600 hover:underline">Forgot Password?</a>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md hover:bg-blue-700 transition-colors duration-200">Login</button>
        </form>
        <p class="mt-6 text-center text-gray-700">Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Sign up</a></p>
    </div>
</body>
</html>
