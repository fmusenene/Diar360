export type ProjectStatus = "Completed" | "In Progress" | "Planning" | "On Hold";

export interface Project {
  id: string;
  name: string;
  description: string;
  status: ProjectStatus;
  location: string;
  startDate: string;
  endDate?: string;
  budget: string;
  client: string;
  image?: string;
}

export const initialProjects: Project[] = [
  {
    id: "1",
    name: "Al Nakheel Residence",
    description: "Luxury waterfront villa with private beach access and infinity pool.",
    status: "Completed",
    location: "Dubai Marina",
    startDate: "2024-01-15",
    endDate: "2025-06-30",
    budget: "$2.4M",
    client: "Al Rashid Group",
  },
  {
    id: "2",
    name: "The Pearl Tower",
    description: "40-story mixed-use development featuring premium apartments and retail spaces.",
    status: "In Progress",
    location: "Business Bay",
    startDate: "2025-03-01",
    budget: "$18M",
    client: "Gulf Properties",
  },
  {
    id: "3",
    name: "Desert Bloom Villas",
    description: "Eco-friendly residential community with 50 sustainable villas.",
    status: "Planning",
    location: "Al Ain",
    startDate: "2026-06-01",
    budget: "$8.5M",
    client: "Green Developments",
  },
  {
    id: "4",
    name: "Sunset Gardens Resort",
    description: "5-star boutique resort with spa facilities and conference center.",
    status: "On Hold",
    location: "Fujairah",
    startDate: "2025-09-15",
    budget: "$12M",
    client: "Hospitality Ventures",
  },
  {
    id: "5",
    name: "Marina Walk Commercial",
    description: "Premium retail and dining destination along the waterfront promenade.",
    status: "In Progress",
    location: "Abu Dhabi",
    startDate: "2025-07-01",
    budget: "$6.2M",
    client: "Capital Investments",
  },
];
