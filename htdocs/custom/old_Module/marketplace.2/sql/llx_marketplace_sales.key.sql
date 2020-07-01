-- Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_rowid (rowid);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_seller (fk_seller);
ALTER TABLE llx_marketplace_sales ADD CONSTRAINT llx_marketplace_sales_fk_seller FOREIGN KEY (fk_seller) REFERENCES llx_societe(rowid);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_product (fk_product);
ALTER TABLE llx_marketplace_sales ADD CONSTRAINT llx_marketplace_sales_fk_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_customer_invoice (fk_customer_invoice);
ALTER TABLE llx_marketplace_sales ADD CONSTRAINT llx_marketplace_sales_fk_customer_invoice FOREIGN KEY (fk_customer_invoice) REFERENCES llx_facture(rowid);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_customer_invoice_line (fk_customer_invoice_line);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_seller_invoice (fk_seller_invoice);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_fk_seller_invoice_line (fk_seller_invoice_line);
ALTER TABLE llx_marketplace_sales ADD CONSTRAINT llx_marketplace_sales_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_marketplace_sales ADD INDEX idx_marketplace_suppliersales_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_marketplace_sales ADD UNIQUE INDEX uk_marketplace_suppliersales_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_marketplace_sales ADD CONSTRAINT llx_marketplace_sales_fk_field FOREIGN KEY (fk_field) REFERENCES llx_marketplace_myotherobject(rowid);

