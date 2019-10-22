package API

import (
	"database/sql"
	"log"
)

type Creds struct {
	User   string `json:"dbUser"`
	Pass   string `json:"dbPass"`
	Name   string `json:"dbName"`
	Host   string `json:"dbHost"`
	Port   string `json:"dbPort"`
	PcName string `json:"pcName"`
}

var db_creds Creds

func SetCredentials(creds Creds) {
	db_creds = creds
}

func OpenDB() *sql.DB {
	dbConnStr := db_creds.User + ":" + db_creds.Pass + "@tcp(" + db_creds.Host + ":" + db_creds.Port + ")/" + db_creds.Name
	db, err := sql.Open("mysql", dbConnStr)
	if err != nil {
		log.Fatal(err)
	}
	err = db.Ping()
	if err != nil {
		log.Fatal("DB not ready")
	}
	return db
}

func CloseDB(db *sql.DB) {
	defer db.Close()
}
