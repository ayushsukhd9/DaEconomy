-- #!sqlite
-- #{ economy

-- #  { init
CREATE TABLE IF NOT EXISTS economy (
    xuid TEXT PRIMARY KEY,
    balance INTEGER NOT NULL
);
-- #  }

-- #  { get
SELECT balance FROM economy WHERE xuid = :xuid;
-- #  }

-- #  { create
INSERT OR IGNORE INTO economy (xuid, balance) VALUES (:xuid, :balance);
-- #  }

-- #  { set
UPDATE economy SET balance = :balance WHERE xuid = :xuid;
-- #  }

-- #  { add
UPDATE economy SET balance = balance + :amount WHERE xuid = :xuid;
-- #  }

-- #  { reduce
UPDATE economy SET balance = balance - :amount WHERE xuid = :xuid;
-- #  }

-- #  { delete
DELETE FROM economy WHERE xuid = :xuid;
-- #  }

-- #}
