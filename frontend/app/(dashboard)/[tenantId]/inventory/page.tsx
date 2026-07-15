"use client";

import { useState } from "react";
import { 
  History, 
  AlertTriangle, 
  Calendar, 
  ArrowUpRight, 
  ArrowDownRight,
  Filter,
  Download
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";

// Mock data for batches
const batches = [
  {
    id: "B-001",
    product: "Premium Basmati Rice 5kg",
    batchNumber: "BN-2024-001",
    expiryDate: "2024-12-15",
    quantity: 50,
    initialQuantity: 100,
    daysToExpiry: 156,
    status: "good"
  },
  {
    id: "B-002",
    product: "Sunflower Oil 1L",
    batchNumber: "BN-2024-052",
    expiryDate: "2024-08-20",
    quantity: 12,
    initialQuantity: 60,
    daysToExpiry: 40,
    status: "expiring-soon"
  },
  {
    id: "B-003",
    product: "Organic Honey 500g",
    batchNumber: "BN-2023-112",
    expiryDate: "2024-07-25",
    quantity: 8,
    initialQuantity: 40,
    daysToExpiry: 14,
    status: "critical"
  }
];

export default function InventoryPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Inventory & Batch Tracking</h1>
          <p className="text-slate-500">Monitor batch levels, expiration dates, and stock movements.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline">
            <History className="mr-2 h-4 w-4" /> Movement Logs
          </Button>
          <Button>
            <Plus className="mr-2 h-4 w-4" /> Manual Adjustment
          </Button>
        </div>
      </div>

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <Card className="border-l-4 border-l-red-500">
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Critical Expiry</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">3</div>
            <p className="text-xs text-slate-500">Expiring within 15 days</p>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-amber-500">
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Expiring Soon</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">8</div>
            <p className="text-xs text-slate-500">Expiring within 60 days</p>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-blue-500">
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Stock Value</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">$42,500</div>
            <p className="text-xs text-slate-500">Total warehouse valuation</p>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-green-500">
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Monthly Turnover</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">2.4x</div>
            <p className="text-xs text-slate-500">Inventory rotation rate</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Active Batches</CardTitle>
                <CardDescription>Real-time status of product batches in the main warehouse.</CardDescription>
              </div>
              <Button variant="outline" size="sm">
                <Filter className="mr-2 h-4 w-4" /> Filter
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Batch Info</TableHead>
                  <TableHead>Product</TableHead>
                  <TableHead>Stock Level</TableHead>
                  <TableHead>Expiry Date</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {batches.map((batch) => (
                  <TableRow key={batch.id}>
                    <TableCell>
                      <div className="font-medium">{batch.batchNumber}</div>
                      <div className="text-[10px] text-slate-400">ID: {batch.id}</div>
                    </TableCell>
                    <TableCell className="text-sm">{batch.product}</TableCell>
                    <TableCell>
                      <div className="w-[100px] space-y-1">
                        <div className="flex justify-between text-[10px]">
                          <span>{batch.quantity} left</span>
                          <span>{Math.round((batch.quantity / batch.initialQuantity) * 100)}%</span>
                        </div>
                        <Progress value={(batch.quantity / batch.initialQuantity) * 100} className="h-1" />
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center text-sm">
                        <Calendar className="mr-2 h-3 w-3 text-slate-400" />
                        {batch.expiryDate}
                      </div>
                      <div className="text-[10px] text-slate-400">{batch.daysToExpiry} days left</div>
                    </TableCell>
                    <TableCell>
                      <Badge 
                        variant={
                          batch.status === "critical" ? "destructive" : 
                          batch.status === "expiring-soon" ? "warning" : "default"
                        }
                      >
                        {batch.status === "critical" ? "Critical" : 
                         batch.status === "expiring-soon" ? "Soon" : "Good"}
                      </Badge>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Stock Alerts</CardTitle>
            <CardDescription>Items below reorder threshold.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {[1, 2, 3].map((i) => (
              <div key={i} className="flex items-start gap-4 p-3 rounded-lg border bg-slate-50/50">
                <div className="p-2 rounded-full bg-amber-100 text-amber-600">
                  <AlertTriangle className="h-4 w-4" />
                </div>
                <div className="flex-1 space-y-1">
                  <p className="text-sm font-medium leading-none">Sunflower Oil 1L</p>
                  <p className="text-xs text-slate-500">Current: 15 units | Reorder: 25</p>
                  <div className="pt-2">
                    <Button size="sm" variant="outline" className="h-7 text-[10px]">Create PO</Button>
                  </div>
                </div>
              </div>
            ))}
            <Button variant="ghost" className="w-full text-xs text-slate-500">View all alerts</Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function Plus(props: any) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M5 12h14" />
      <path d="M12 5v14" />
    </svg>
  )
}
