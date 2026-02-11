# CrawlMark

Scrape URLs to markdown with [Firecrawl](https://firecrawl.dev). Crawl and save web pages as clean, structured documents — perfect for archiving, research, or feeding AI.

## Features

- **URL to Markdown** — Paste any URL and get structured markdown (headings, links, text)
- **Text-only mode** — Strip images for smaller, faster documents
- **Re-scrape** — Refresh content anytime to keep documents up to date
- **Download** — Export markdown files directly
- **Dashboard** — Overview of URLs and documents

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- [Firecrawl](https://firecrawl.dev) API key

## Setup

### 1. Clone the repository

```bash
git clone https://github.com/fakharkhan/urlcrawler.git
cd urlcrawler
```

### 2. Run the setup script

```bash
composer setup
```

This will:

- Install PHP dependencies
- Copy `.env.example` to `.env` (if needed)
- Generate an application key
- Run database migrations
- Install npm dependencies
- Build frontend assets

### 3. Configure environment

Edit `.env` and add your Firecrawl API key:

```env
FIRE_CRAWL_API_KEY=your_api_key_here
```

Get a free API key at [firecrawl.dev](https://firecrawl.dev).

### 4. Database (optional)

The default configuration uses SQLite. To use MySQL or PostgreSQL, update the `DB_*` variables in `.env` and run migrations again:

```bash
php artisan migrate --force
```

## Running the application

### Development

Starts the PHP server, queue worker, log tail, and Vite dev server:

```bash
composer run dev
```

Then open [http://localhost:8000](http://localhost:8000).

### Production

1. Build assets and optimize:

   ```bash
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. Run the web server and queue worker:

   ```bash
   php artisan serve          # or your web server
   php artisan queue:work     # in a separate process
   ```

## Project structure

| Path                 | Description                    |
|----------------------|--------------------------------|
| `app/Services/`      | Firecrawl scraping service     |
| `app/Http/Controllers/` | Document & scrape logic     |
| `resources/js/pages/documents/` | Document list and view |
| `routes/web.php`     | Web routes                    |

## Testing

```bash
php artisan test
```

## Tech stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Inertia.js, React 19, Tailwind CSS v4
- **Scraping:** [Firecrawl](https://firecrawl.dev) API

## License

MIT License. See [LICENSE](LICENSE) for details.
