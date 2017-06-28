package Posts

import (
  "net/http"
  "log"
  _ "github.com/go-sql-driver/mysql"
  "questlog/DBUtils"
)

type Post struct {
  Pid int `json:"pid"`
  Qid int `json:"qid"`
  Uid int `json:"uid"`
  Text string `json:"text"`
  Stamp int `json:"stamp"`
}

func GetPosts(w http.ResponseWriter, qid int, start int, length int) []Post {
  var posts []Post
  db := DBUtils.OpenDB();
  rows, err := db.Query("select pid,qid,uid,post_text,UNIX_TIMESTAMP(post_date) from posts WHERE qid = ? ORDER BY post_date DESC LIMIT ?, ?", qid, start, length)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    post := Post{}
    err := rows.Scan(&post.Pid, &post.Qid, &post.Uid, &post.Text, &post.Stamp)
    if err != nil {
      log.Fatal(err)
    }
    posts = append(posts, post)
  }
  DBUtils.CloseDB(db)
  return posts
}