CREATE OR REPLACE PROCEDURE delete_old_sessions(
    p_date DATE
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_session_count INT;
BEGIN
    SELECT COUNT(*) INTO v_session_count
    FROM Session
    WHERE session_data < p_date;
    IF v_session_count = 0 THEN
        RAISE NOTICE 'Нет сеансов до % для удаления', p_date;
        RETURN;
    END IF;
    BEGIN
        DELETE FROM Ticket
        WHERE session_id IN (
            SELECT session_id
            FROM Session
            WHERE session_data < p_date
        );
        DELETE FROM Purchase
        WHERE session_id IN (
            SELECT session_id
            FROM Session
            WHERE session_data < p_date
        );
        DELETE FROM Session
        WHERE session_data < p_date;
        COMMIT;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            RAISE EXCEPTION 'Ошибка при удалении сеансов: %', SQLERRM;
    END;
END;
$$;

CREATE OR REPLACE FUNCTION update_ticket_status_on_refunded()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE Ticket
    SET ticket_status = 'refunded',
        refund_id = NEW.refund_id
    WHERE ticket_id = NEW.ticket_id;
    UPDATE Seat
    SET ticket_id = NULL
    WHERE ticket_id = NEW.ticket_id;
    RETURN NEW;
END;
$$;
CREATE TRIGGER update_ticket_on_refunded
AFTER INSERT ON Refund
FOR EACH ROW
EXECUTE FUNCTION update_ticket_status_on_refunded();

CREATE OR REPLACE PROCEDURE add_ticket_purchase(
    p_goer_email VARCHAR(320),
    p_session_id INT4,
    p_seat_id INT4
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_purchase_id INT4;
    v_session_exists BOOLEAN;
    v_goer_exists BOOLEAN;
    v_seat_available BOOLEAN;
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM Goer WHERE goer_email = p_goer_email
    ) INTO v_goer_exists;
    IF NOT v_goer_exists THEN
        RAISE EXCEPTION 'Пользователь с email % не найден', p_goer_email;
    END IF;
    SELECT EXISTS (
        SELECT 1 FROM Session WHERE session_id = p_session_id
    ) INTO v_session_exists;
    IF NOT v_session_exists THEN
        RAISE EXCEPTION 'Сеанс с ID % не найден', p_session_id;
    END IF;
    SELECT ticket_id IS NULL INTO v_seat_available
    FROM Seat
    WHERE seat_id = p_seat_id
    AND hall_id = (SELECT hall_id FROM Session WHERE session_id = p_session_id);
    IF NOT v_seat_available THEN
        RAISE EXCEPTION 'Место с ID % уже занято или недоступно', p_seat_id;
    END IF;
    BEGIN
        INSERT INTO Purchase (session_id, goer_email, purchase_date)
        VALUES (p_session_id, p_goer_email, CURRENT_DATE)
        RETURNING purchase_id INTO v_purchase_id;
        INSERT INTO Ticket (session_id, seat_id, purchase_id, ticket_status)
        VALUES (p_session_id, p_seat_id, v_purchase_id, 'confirmed');
        UPDATE Seat
        SET ticket_id = (SELECT ticket_id FROM Ticket WHERE purchase_id = v_purchase_id AND seat_id = p_seat_id)
        WHERE seat_id = p_seat_id;
        COMMIT;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            RAISE EXCEPTION 'Ошибка при создании покупки: %', SQLERRM;
    END;
END;
$$;