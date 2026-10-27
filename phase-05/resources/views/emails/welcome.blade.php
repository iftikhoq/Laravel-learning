<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Laravel Learning E-Store</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: #4f46e5; padding: 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .body { padding: 30px; color: #374151; line-height: 1.6; }
        .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Welcome, {{ $name }}! 🎉</h1>
        </div>
        <div class="body">
            <p>Thank you for registering at <strong>Laravel Learning E-Store</strong>.</p>
            <p>Your account has been configured with role: <code>{{ $role }}</code>.</p>
            @if($role === 'admin')
                <p>You have elevated administrative privileges to manage catalog inventories, update product specifications, and supervise user activity.</p>
            @else
                <p>You can now browse our live catalog, add products to your wishlist, and experience seamless token-authenticated shopping.</p>
            @endif
            <p style="text-align: center;">
                <a href="{{ $loginUrl ?? 'http://localhost:5173' }}" class="button">Access Platform</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Laravel Learning E-Commerce Engine. All rights reserved.
        </div>
    </div>
</body>
</html>
