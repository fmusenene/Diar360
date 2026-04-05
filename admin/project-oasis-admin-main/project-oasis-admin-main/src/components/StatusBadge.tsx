import { cn } from "@/lib/utils";
import type { ProjectStatus } from "@/lib/projectsData";

const statusConfig: Record<ProjectStatus, { bg: string; text: string; dot: string }> = {
  Completed: { bg: "bg-status-completed/15", text: "text-status-completed", dot: "bg-status-completed" },
  "In Progress": { bg: "bg-status-in-progress/15", text: "text-status-in-progress", dot: "bg-status-in-progress" },
  Planning: { bg: "bg-status-planning/15", text: "text-status-planning", dot: "bg-status-planning" },
  "On Hold": { bg: "bg-status-on-hold/15", text: "text-status-on-hold", dot: "bg-status-on-hold" },
};

export function StatusBadge({ status }: { status: ProjectStatus }) {
  const config = statusConfig[status];
  return (
    <span className={cn("inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold", config.bg, config.text)}>
      <span className={cn("w-1.5 h-1.5 rounded-full", config.dot)} />
      {status}
    </span>
  );
}
