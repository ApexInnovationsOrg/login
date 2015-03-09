$env = $app->detectEnvironment(function()
{
    return getenv('APP_ENV') ?: 'production';
});