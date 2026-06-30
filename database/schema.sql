CREATE TABLE IF NOT EXISTS site_settings (
  id text PRIMARY KEY,
  phone text NOT NULL DEFAULT '',
  email text NOT NULL DEFAULT '',
  address text NOT NULL DEFAULT '',
  working_hours text NOT NULL DEFAULT '',
  legal_info text NOT NULL DEFAULT '',
  socials jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS pages (
  id text PRIMARY KEY,
  type text NOT NULL,
  title text NOT NULL,
  slug text NOT NULL UNIQUE,
  menu_description text NOT NULL DEFAULT '',
  seo_title text NOT NULL DEFAULT '',
  seo_description text NOT NULL DEFAULT '',
  status text NOT NULL DEFAULT 'draft',
  cover text NOT NULL DEFAULT '',
  content jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS pages_type_idx ON pages(type);
CREATE INDEX IF NOT EXISTS pages_status_idx ON pages(status);

CREATE TABLE IF NOT EXISTS reviews (
  id text PRIMARY KEY,
  author text NOT NULL,
  date text NOT NULL DEFAULT '',
  text text NOT NULL,
  avatar text NOT NULL DEFAULT '',
  category text NOT NULL DEFAULT 'all',
  sort_order integer NOT NULL DEFAULT 0,
  status text NOT NULL DEFAULT 'published',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS reviews_status_idx ON reviews(status);
CREATE INDEX IF NOT EXISTS reviews_sort_order_idx ON reviews(sort_order);

CREATE TABLE IF NOT EXISTS leads (
  id text PRIMARY KEY,
  status text NOT NULL DEFAULT 'new',
  name text NOT NULL DEFAULT '',
  phone text NOT NULL DEFAULT '',
  email text NOT NULL DEFAULT '',
  comment text NOT NULL DEFAULT '',
  product text NOT NULL DEFAULT '',
  plan text NOT NULL DEFAULT '',
  contact_method text NOT NULL DEFAULT '',
  source text NOT NULL DEFAULT '',
  form text NOT NULL DEFAULT '',
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS leads_status_idx ON leads(status);
CREATE INDEX IF NOT EXISTS leads_created_at_idx ON leads(created_at);
