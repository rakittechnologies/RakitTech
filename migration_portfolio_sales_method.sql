-- Jalankan satu kali melalui phpMyAdmin pada database RuangWeb lama.
ALTER TABLE projects ADD COLUMN IF NOT EXISTS sales_method VARCHAR(120) NOT NULL DEFAULT 'Project-Based' AFTER website_type;

-- Sesuaikan nama model lama dan petakan data portofolio yang sudah ada.
UPDATE projects SET sales_method = 'Website Retainer' WHERE sales_method = 'Website Maintenance';
UPDATE projects
SET sales_method = CASE
  WHEN LOWER(CONCAT(COALESCE(title,''),' ',COALESCE(category,''),' ',COALESCE(description,''))) REGEXP 'saas|website builder|platform pembuat website|website builder' THEN 'SaaS'
  WHEN LOWER(CONCAT(COALESCE(title,''),' ',COALESCE(category,''),' ',COALESCE(description,''))) REGEXP 'waas|website as a service|sewa website|website berlangganan|langganan website' THEN 'Website as a Service (WaaS)'
  WHEN LOWER(CONCAT(COALESCE(title,''),' ',COALESCE(category,''),' ',COALESCE(description,''))) REGEXP 'maintenance|retainer|pemeliharaan website|perawatan website' THEN 'Website Retainer'
  ELSE 'Project-Based'
END;
