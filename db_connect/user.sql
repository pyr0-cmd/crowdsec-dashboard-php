-- Create a users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
	password TEXT NOT NULL, -- Store hashed password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a sample user (You should replace this with proper user registration logic)
INSERT INTO users (username, password) 
VALUES ('admin', crypt('P@ssw0rd123', gen_salt('bf')))
ON CONFLICT (username) DO NOTHING;  -- Prevents insertion of duplicate admin user

-- Grant basic permissions to the public user (adjust as necessary)
GRANT SELECT, INSERT, UPDATE, DELETE ON users TO crowdsec;
