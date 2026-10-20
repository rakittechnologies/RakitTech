-- Jalankan hanya jika aplikasi tidak dapat menambahkan kolom otomatis.
ALTER TABLE projects ADD COLUMN business_model_status VARCHAR(80) NOT NULL DEFAULT 'Contoh konsep';
ALTER TABLE projects ADD COLUMN sales_method VARCHAR(120) NOT NULL DEFAULT 'Konsultasi melalui WhatsApp';
