-- =============================================================================
-- PSF – stock_cache trigger
-- File: database/sql/stock_cache_trigger.sql
-- Apply with: php artisan db:apply-triggers
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Helper: upsert a single (location, product, batch) row in stock_cache,
-- adding `delta` to quantity. Raises an exception if the result < 0.
-- -----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_update_stock_cache()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_new_qty NUMERIC(10,2);
BEGIN
    -- -------------------------------------------------------------------------
    -- 1. DEDUCT from from_location_id (outgoing stock)
    -- -------------------------------------------------------------------------
    IF NEW.from_location_id IS NOT NULL THEN

        IF NEW.batch_id IS NOT NULL THEN
            INSERT INTO stock_cache (location_id, product_id, batch_id, quantity)
            VALUES (NEW.from_location_id, NEW.product_id, NEW.batch_id, -NEW.quantity)
            ON CONFLICT (location_id, product_id, batch_id) WHERE batch_id IS NOT NULL
            DO UPDATE SET quantity = stock_cache.quantity - NEW.quantity
            RETURNING quantity INTO v_new_qty;
        ELSE
            INSERT INTO stock_cache (location_id, product_id, batch_id, quantity)
            VALUES (NEW.from_location_id, NEW.product_id, NULL, -NEW.quantity)
            ON CONFLICT (location_id, product_id) WHERE batch_id IS NULL
            DO UPDATE SET quantity = stock_cache.quantity - NEW.quantity
            RETURNING quantity INTO v_new_qty;
        END IF;

        IF v_new_qty < 0 THEN
            RAISE EXCEPTION
                'Insufficient stock: location_id=%, product_id=%, batch_id=%, available would be %',
                NEW.from_location_id, NEW.product_id, NEW.batch_id, v_new_qty;
        END IF;

    END IF;

    -- -------------------------------------------------------------------------
    -- 2. ADD to to_location_id (incoming stock)
    -- -------------------------------------------------------------------------
    IF NEW.to_location_id IS NOT NULL THEN

        IF NEW.batch_id IS NOT NULL THEN
            INSERT INTO stock_cache (location_id, product_id, batch_id, quantity)
            VALUES (NEW.to_location_id, NEW.product_id, NEW.batch_id, NEW.quantity)
            ON CONFLICT (location_id, product_id, batch_id) WHERE batch_id IS NOT NULL
            DO UPDATE SET quantity = stock_cache.quantity + NEW.quantity;
        ELSE
            INSERT INTO stock_cache (location_id, product_id, batch_id, quantity)
            VALUES (NEW.to_location_id, NEW.product_id, NULL, NEW.quantity)
            ON CONFLICT (location_id, product_id) WHERE batch_id IS NULL
            DO UPDATE SET quantity = stock_cache.quantity + NEW.quantity;
        END IF;

    END IF;

    RETURN NEW;
END;
$$;

-- -----------------------------------------------------------------------------
-- Trigger: fires AFTER each row INSERT on inventory_movements
-- -----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_inventory_movements_after_insert ON inventory_movements;

CREATE TRIGGER trg_inventory_movements_after_insert
    AFTER INSERT ON inventory_movements
    FOR EACH ROW
    EXECUTE FUNCTION fn_update_stock_cache();
