import { useState, useEffect } from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import type { Project, ProjectStatus } from "@/lib/projectsData";

const statuses: ProjectStatus[] = ["Completed", "In Progress", "Planning", "On Hold"];

interface ProjectFormDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  project?: Project | null;
  onSave: (project: Omit<Project, "id"> & { id?: string }) => void;
}

export function ProjectFormDialog({ open, onOpenChange, project, onSave }: ProjectFormDialogProps) {
  const [form, setForm] = useState({
    name: "",
    description: "",
    status: "Planning" as ProjectStatus,
    location: "",
    startDate: "",
    endDate: "",
    budget: "",
    client: "",
  });

  useEffect(() => {
    if (project) {
      setForm({
        name: project.name,
        description: project.description,
        status: project.status,
        location: project.location,
        startDate: project.startDate,
        endDate: project.endDate || "",
        budget: project.budget,
        client: project.client,
      });
    } else {
      setForm({ name: "", description: "", status: "Planning", location: "", startDate: "", endDate: "", budget: "", client: "" });
    }
  }, [project, open]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave({ ...form, ...(project ? { id: project.id } : {}) });
    onOpenChange(false);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-lg bg-popover">
        <DialogHeader>
          <DialogTitle className="font-heading text-xl">
            {project ? "Edit Project" : "New Project"}
          </DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <Label htmlFor="name">Project Name</Label>
              <Input id="name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
            </div>
            <div className="col-span-2">
              <Label htmlFor="description">Description</Label>
              <Textarea id="description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={3} required />
            </div>
            <div>
              <Label htmlFor="status">Status</Label>
              <Select value={form.status} onValueChange={(v) => setForm({ ...form, status: v as ProjectStatus })}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  {statuses.map((s) => (
                    <SelectItem key={s} value={s}>{s}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label htmlFor="location">Location</Label>
              <Input id="location" value={form.location} onChange={(e) => setForm({ ...form, location: e.target.value })} required />
            </div>
            <div>
              <Label htmlFor="startDate">Start Date</Label>
              <Input id="startDate" type="date" value={form.startDate} onChange={(e) => setForm({ ...form, startDate: e.target.value })} required />
            </div>
            <div>
              <Label htmlFor="endDate">End Date</Label>
              <Input id="endDate" type="date" value={form.endDate} onChange={(e) => setForm({ ...form, endDate: e.target.value })} />
            </div>
            <div>
              <Label htmlFor="budget">Budget</Label>
              <Input id="budget" value={form.budget} onChange={(e) => setForm({ ...form, budget: e.target.value })} required />
            </div>
            <div>
              <Label htmlFor="client">Client</Label>
              <Input id="client" value={form.client} onChange={(e) => setForm({ ...form, client: e.target.value })} required />
            </div>
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit">{project ? "Save Changes" : "Create Project"}</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
