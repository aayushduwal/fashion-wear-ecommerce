-- Update orders table to support Khalti payment integration
-- Add order_id field for custom order identifiers and khalti_idx for Khalti transaction IDs
ALTER TABLE orders 
ADD COLUMN order_id VARCHAR(50) UNIQUE AFTER id,
ADD COLUMN khalti_idx VARCHAR(100) AFTER payment_ref;

-- Update payment_method column to support khalti if needed
-- (Skip this if you want to keep varchar(50) instead of ENUM)
-- ALTER TABLE orders MODIFY payment_method ENUM('cod', 'esewa', 'khalti') DEFAULT 'cod';
