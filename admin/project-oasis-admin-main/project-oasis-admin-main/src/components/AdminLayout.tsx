import { useState } from "react";
import { AdminSidebar } from "./AdminSidebar";
import { TopNavbar } from "./TopNavbar";
import { motion } from "framer-motion";

interface AdminLayoutProps {
  children: React.ReactNode;
}

export function AdminLayout({ children }: AdminLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);

  return (
    <div className="min-h-screen bg-background">
      <AdminSidebar collapsed={collapsed} onToggle={() => setCollapsed(!collapsed)} />
      <motion.div
        animate={{ marginLeft: collapsed ? 72 : 260 }}
        transition={{ duration: 0.3, ease: "easeInOut" }}
        className="min-h-screen flex flex-col"
      >
        <TopNavbar />
        <main className="flex-1">
          {children}
        </main>
      </motion.div>
    </div>
  );
}
