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
  Cid int `json:"cid"`
  Poster string `json:"poster"`
  Text string `json:"text"`
  Stamp int `json:"stamp"`
}

func GetPosts(w http.ResponseWriter, qid int, start int, length int, order string) []Post {
  var posts []Post
  db := DBUtils.OpenDB();
  log.Println(order);
  rows, err := db.Query("select pid,qid,uid,cid,post_text,UNIX_TIMESTAMP(post_date) from posts WHERE qid = ? ORDER BY post_date " + order + " LIMIT ?, ?", qid, start, length)
  if err != nil {
    log.Fatal(err)
  }
  defer rows.Close()
  for rows.Next() {
    post := Post{}
    err := rows.Scan(&post.Pid, &post.Qid, &post.Uid, &post.Cid, &post.Text, &post.Stamp)
    if err != nil {
      log.Fatal(err)
    }
    if post.Cid == 0 {
      db.QueryRow("select login_name from users where uid = ?", post.Uid).Scan(&post.Poster)
      post.Poster = "GM - " + post.Poster
    } else {
      db.QueryRow("select char_name from characters where cid = ?", post.Cid).Scan(&post.Poster) 
    }
    posts = append(posts, post)
  }
  DBUtils.CloseDB(db)
  return posts
}