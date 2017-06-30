package DBUtils

import (
  "database/sql"
  "log"
)

const (
  DB_USER     = "questlog"
  DB_PASSWORD = "Z6c2a2yyb2vG"
  DB_NAME     = "questlog"
  //DB_HOST     = "tcp(localhost:3306)"
  DB_HOST     = "tcp(52.4.79.128:3306)"
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