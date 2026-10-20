ALTER TABLE projects MODIFY theme_color VARCHAR(7) NOT NULL DEFAULT '#5B77EB';
UPDATE projects SET theme_color = CASE theme_color WHEN 'blue' THEN '#5B77EB' WHEN 'green' THEN '#18AE70' WHEN 'orange' THEN '#F58B2C' WHEN 'pink' THEN '#E15B9E' WHEN 'violet' THEN '#8B5CF6' ELSE theme_color END WHERE theme_color IN ('blue','green','orange','pink','violet');
