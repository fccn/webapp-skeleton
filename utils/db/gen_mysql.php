<?

#--- user
$db->exec("
  CREATE TABLE IF NOT EXISTS user (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name TEXT,
    email TEXT,
    localuser BIT,
    auth_source TEXT,
    created_at DATETIME,
    last_login DATETIME,
    in_session BIT,
    session_count INTEGER,
    locale TEXT,
    CONSTRAINT user_id PRIMARY KEY (id)
  );"
);
echo "generated table user \n";
#--- app_log
$db->exec("
	CREATE TABLE IF NOT EXISTS app_log (
  	id INTEGER NOT NULL AUTO_INCREMENT,
    label TEXT,
    timestamp TIMESTAMP,
		log_entity_type_id INTEGER,
		entity_id INTEGER,
		entity TEXT,
		session_id INTEGER,
		user_id INTEGER,
		param TEXT,
    CONSTRAINT app_log_id PRIMARY KEY (id)
  );"
);
echo "generated table app_log \n";
#--- log_entity_type
$db->exec("
	CREATE TABLE IF NOT EXISTS log_entity_type (
  	id INTEGER NOT NULL AUTO_INCREMENT,
    label TEXT,
		name TEXT,
    CONSTRAINT log_entity_type PRIMARY KEY (id)
  );"
);
echo "generated table log_entity_type \n";
#--- add project specific tables below
