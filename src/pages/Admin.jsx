import { useEffect, useState } from "react";
import "./Admin.css";
import { API, getMediaUrl, postJson } from "../utils";

const MEDIA_LABEL = { image: "🖼️ Ảnh", video: "🎬 Video", file: "📎 Tệp" };

function Admin() {
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem("user") || "null");
  } catch {
    user = null;
  }

  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [deletingId, setDeletingId] = useState(null);

  // LẤY TẤT CẢ BÀI VIẾT

  useEffect(() => {
    if (!user?.id) {
      setLoading(false);
      return;
    }

    const fetchPosts = async () => {
      try {
        const response = await fetch(`${API}/posts/posts.php?user_id=${user.id}`);
        const data = await response.json();

        setPosts(Array.isArray(data) ? data : []);
      } catch (error) {
        console.log("Lỗi lấy bài viết:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchPosts();
  }, [user?.id]);

  // XÓA BÀI VIẾT

  const handleDelete = async (post) => {
    if (!window.confirm(`Xóa bài viết của "${post.name}"? Thao tác này không thể hoàn tác.`)) {
      return;
    }

    setDeletingId(post.id);

    try {
      await postJson(`${API}/posts/manage.php`, { action: "delete", post_id: post.id, user_id: user.id });

      setPosts((current) => current.filter((item) => item.id !== post.id));
    } catch (error) {
      alert(error.message);
    } finally {
      setDeletingId(null);
    }
  };

  // TÌM KIẾM

  const keyword = search.toLowerCase();

  const filteredPosts = posts.filter(
    (post) => post.content?.toLowerCase().includes(keyword) || post.name?.toLowerCase().includes(keyword),
  );

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div>
          <h1>🛡️ Quản trị ITConnect</h1>
          <span>Xin chào, {user?.name || "Quản trị viên"}</span>
        </div>

        <button
          className="admin-logout"
          onClick={() => {
            localStorage.removeItem("user");
            window.location.href = "/";
          }}
        >
          🚪 Đăng xuất
        </button>
      </header>

      <main className="admin-main">
        <div className="admin-toolbar">
          <h2>Quản lý bài viết ({posts.length})</h2>

          <input
            type="text"
            placeholder="Tìm theo nội dung hoặc tên người đăng..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>

        {loading ? (
          <p className="admin-empty">Đang tải bài viết...</p>
        ) : filteredPosts.length === 0 ? (
          <p className="admin-empty">Không có bài viết nào.</p>
        ) : (
          filteredPosts.map((post) => (
            <article className="admin-post" key={post.id}>
              <div className="admin-post-info">
                <div className="admin-post-top">
                  <strong>{post.name}</strong>
                  <span>{post.created_at ? new Date(post.created_at).toLocaleString("vi-VN") : ""}</span>
                  {MEDIA_LABEL[post.media_type] && <em>{MEDIA_LABEL[post.media_type]}</em>}
                </div>

                <p>{post.content || "(Bài viết không có nội dung chữ)"}</p>

                {post.media_type === "image" && post.image && (
                  <img src={getMediaUrl(post.image)} alt="Ảnh bài viết" className="admin-post-image" />
                )}

                {post.media_type === "video" && post.file && (
                  <video src={getMediaUrl(post.file)} controls className="admin-post-video" />
                )}

                {post.media_type === "file" && post.file && (
                  <div className="admin-post-file">
                    <span>📎 {post.file.split("/").pop()}</span>
                    <a
                      href={getMediaUrl(post.file)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="admin-file-download"
                    >
                      Mở / Tải tệp
                    </a>
                  </div>
                )}

                <small>
                  ❤️ {post.like_count} lượt thích · 💬 {post.comment_count} bình luận
                </small>
              </div>

              <button
                className="admin-delete"
                disabled={deletingId === post.id}
                onClick={() => handleDelete(post)}
              >
                {deletingId === post.id ? "Đang xóa..." : "🗑️ Xóa"}
              </button>
            </article>
          ))
        )}
      </main>
    </div>
  );
}

export default Admin;
