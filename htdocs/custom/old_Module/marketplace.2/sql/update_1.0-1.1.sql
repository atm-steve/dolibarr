ALTER TABLE llx_marketplace_sales CHANGE COLUMN discount_amount discount_rate real default 0;
ALTER TABLE llx_marketplace_sales ADD COLUMN discount_amount  real after discount_rate;
