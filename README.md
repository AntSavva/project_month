# Kubera PHP site

Production PHP site for Kubera. The repository root is the web root, so the site files are no longer nested inside `public_html`.

## Structure

- `index.php` - front controller
- `.htaccess` - redirects, routing and security headers
- `app/` - PHP helpers, storage, admin panel and auth
- `assets/` - CSS, fonts, icons and images
- `data/site.json` - site content
- `uploads/` - uploaded public images

## Deployment

After this structure change, the repository can be placed directly in the hosting web root:

```bash
cd ~/kubera-dom.ru/public_html
git pull
```

The old Next.js source, build scripts, Node.js package files, and intermediate `public_html/` folder are not used.
