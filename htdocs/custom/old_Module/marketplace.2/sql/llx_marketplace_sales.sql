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


CREATE TABLE llx_marketplace_sales(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_seller integer, 
	fk_product integer, 
	price double(24,8) DEFAULT NULL, -- price brut HT of line
    discount_rate real DEFAULT NULL, -- Discount rate %
    discount_amount double(24,8) DEFAULT NULL, -- Discount amount HT
    care_rate real DEFAULT NULL, -- seller take over the xx% 
    care_amount double(24,8) DEFAULT NULL, -- seller take over the xx€ 
	collection_rate double(24,8) DEFAULT NULL,  -- Collection rate on sale
	collection_amount double(24,8) DEFAULT NULL,  -- Collection amount
    tax_rate double(24,8) DEFAULT NULL,    -- Tax rate for sale
    tax_total double(24,8) DEFAULT NULL,     -- amount of taxes for sale
    retrocession_amount double(24,8) DEFAULT NULL, -- seller will be paid xx€
	fk_customer_invoice integer, 
	fk_customer_invoice_line integer, 
	fk_seller_invoice integer,  -- fk of supplier invoice
	fk_seller_invoice_line integer, -- fk of supplier  
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;