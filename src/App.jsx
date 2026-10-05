import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import Login from "./pages/Login";
import Register from "./pages/Register";
import Home from "./pages/Home";
import ProtectedRoute from "./ProtectedRoute";
import Account from "./pages/Account";
import Friends from "./pages/Friends";
import Messages from "./pages/Messages";
import Notifications from "./pages/Notifications";
import Admin from "./pages/Admin";
import { isAdmin, homePath } from "./utils";

// Chỉ cho admin vào (kiểm tra mỗi lần vào trang, không chỉ lúc tải web)
function AdminRoute({ children }) {
  return isAdmin() ? children : <Navigate to="/" replace />;
}

function App() {
  const user = localStorage.getItem("user");

  return (
    <BrowserRouter>
      <Routes>
        {/* TRANG CHỦ */}

        <Route path="/" element={user ? <Navigate to={homePath()} replace /> : <Login />} />

        {/* ĐĂNG NHẬP */}

        <Route path="/login" element={user ? <Navigate to={homePath()} replace /> : <Login />} />

        {/* ĐĂNG KÝ */}

        <Route path="/register" element={user ? <Navigate to={homePath()} replace /> : <Register />} />

        {/* HOME */}

        <Route
          path="/home"
          element={
            <ProtectedRoute>
              <Home />
            </ProtectedRoute>
          }
        />

        {/* BẠN BÈ */}

        <Route
          path="/friends"
          element={
            <ProtectedRoute>
              <Friends />
            </ProtectedRoute>
          }
        />

        {/* TIN NHẮN */}

        <Route
          path="/messages"
          element={
            <ProtectedRoute>
              <Messages />
            </ProtectedRoute>
          }
        />

        {/* TÀI KHOẢN */}

        <Route
          path="/account"
          element={
            <ProtectedRoute>
              <Account />
            </ProtectedRoute>
          }
        />

        {/* THÔNG BÁO */}

        <Route
          path="/notifications"
          element={
            <ProtectedRoute>
              <Notifications />
            </ProtectedRoute>
          }
        />

        {/* QUẢN TRỊ (CHỈ ADMIN) */}

        <Route
          path="/admin"
          element={
            <AdminRoute>
              <Admin />
            </AdminRoute>
          }
        />

        {/* ĐƯỜNG DẪN KHÔNG TỒN TẠI */}

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
