<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			"

CREATE TABLE `fixed_asset_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_amount` decimal(14,2) NOT NULL,
  `accumulated_depreciation` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monthly_counts` int NOT NULL DEFAULT '0',
  `admin_depreciation_percentage` decimal(14,2) NOT NULL DEFAULT '0.00',
  `manufacturing_depreciation_percentage` decimal(14,2) NOT NULL DEFAULT '0.00',
  `product_allocations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `monthly_product_allocations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_as_revenue_percentages` bigint unsigned NOT NULL DEFAULT '1',
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `monthly_depreciation` decimal(8,2) NOT NULL DEFAULT '0.00',
  `admin_depreciations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `manufacturing_depreciations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `monthly_accumulated_depreciations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  CONSTRAINT `fixed_asset_opening_balances_chk_1` CHECK (json_valid(`product_allocations`)),
  CONSTRAINT `fixed_asset_opening_balances_chk_2` CHECK (json_valid(`monthly_product_allocations`)),
  CONSTRAINT `fixed_asset_opening_balances_chk_3` CHECK (json_valid(`admin_depreciations`)),
  CONSTRAINT `fixed_asset_opening_balances_chk_4` CHECK (json_valid(`statement`)),
  CONSTRAINT `fixed_asset_opening_balances_chk_5` CHECK (json_valid(`manufacturing_depreciations`)),
  CONSTRAINT `fixed_asset_opening_balances_chk_6` CHECK (json_valid(`monthly_accumulated_depreciations`))
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
",
"
CREATE TABLE `long_term_loan_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `installments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `long_term_loan_opening_balances_chk_1` CHECK (json_valid(`interests`)),
  CONSTRAINT `long_term_loan_opening_balances_chk_2` CHECK (json_valid(`installments`)),
  CONSTRAINT `long_term_loan_opening_balances_chk_3` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 
"
CREATE TABLE `other_debtors_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `other_debtors_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `other_debtors_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 





"
CREATE TABLE `supplier_payable_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `supplier_payable_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `supplier_payable_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 



"
CREATE TABLE `other_long_term_liabilities_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `other_long_term_liabilities_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `other_long_term_liabilities_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 




"
CREATE TABLE `cash_and_bank_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cash_and_bank_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `customer_receivable_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `expected_credit_loss` decimal(14,2) NOT NULL DEFAULT '0.00',
 `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `cash_and_bank_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `cash_and_bank_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",


"
CREATE TABLE `equity_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paid_up_capital_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `legal_reserve_extended` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `paid_up_capital_extended` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `legal_reserve` decimal(14,2) NOT NULL DEFAULT '0.00',
  `retained_earnings` decimal(14,2) NOT NULL DEFAULT '0.00',
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `equity_opening_balances_chk_1` CHECK (json_valid(`legal_reserve_extended`)),
  CONSTRAINT `equity_opening_balances_chk_2` CHECK (json_valid(`paid_up_capital_extended`)),
  CONSTRAINT `equity_opening_balances_chk_3` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 



"
CREATE TABLE `other_credits_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `other_credits_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `other_credits_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
 

"
CREATE TABLE `vat_and_credit_withhold_tax_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vat_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `credit_withhold_taxes` decimal(14,2) NOT NULL DEFAULT '0.00',
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `corporate_taxes_payable` decimal(14,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",


"
CREATE TABLE `other_long_term_assets_opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `study_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '(DC2Type:json)',
  PRIMARY KEY (`id`),
  CONSTRAINT `other_long_term_assets_opening_balances_chk_1` CHECK (json_valid(`payload`)),
  CONSTRAINT `other_long_term_assets_opening_balances_chk_2` CHECK (json_valid(`statement`))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"

		] as $query){
			DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->statement($query);
			
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
