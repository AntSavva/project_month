# Kubera PHP site

Production site for Kubera. The live application is a plain PHP site located in `public_html`.

## Structure

- `public_html/index.php` - front controller
- `public_html/app/` - PHP helpers, storage, admin panel and auth
- `public_html/assets/` - CSS, fonts, icons and images
- `public_html/data/site.json` - site content
- `public_html/uploads/` - uploaded public images

## Deployment

On Beget, update the repository copy and copy changed files into the live `public_html` directory:

```bash
cd ~/project_month
git pull
```

This repository no longer contains the old Next.js source, build scripts, or Node.js package files.
