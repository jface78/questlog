package DBUtils

import (
  "database/sql"
  "log"
)

const (
  DB_USER     = "USERNAME_HERE"
  DB_PASSWORD = "PASSWORD_HERE"
  DB_NAME     = "DATABASE_HERE"
  DB_HOST     = "tcp(localhost:3306)"
)
func OpenDB() *sql.DB {
  var connectStr = DB_USER + ":" + DB_PASSWORD + "@" + DB_HOST + "/" + DB_NAME;
  db, err := sql.Open("mysql", connectStr)
  if err != nil {
    log.Fatal(err)
  }
  
  err = db.Ping()
  if err != nil {
    log.Fatal("DB not ready")
  }
  return db;
}

func CloseDB(db *sql.DB) {
  defer db.Close()
}