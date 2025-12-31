delimiter ;
drop trigger if exists `insert_sales_gatherings` ;
DELIMITER //
create trigger  `insert_sales_gatherings` before insert on `sales_gathering` for each row 
BEGIN
	set new.day_name  = DAYNAME(new.date);
END //



delimiter ;
drop trigger if exists update_sales_gatherings ;
delimiter // 
create trigger  `update_sales_gatherings` before update on `sales_gathering` for each row 
BEGIN
		set new.day_name  = DAYNAME(new.date);
END//
