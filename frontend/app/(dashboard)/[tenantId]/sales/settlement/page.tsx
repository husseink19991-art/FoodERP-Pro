"use client";

import { useState } from "react";
import { 
  CheckCircle2, 
  AlertCircle, 
  Truck, 
  Calendar, 
  DollarSign, 
  FileText,
  Search,
  Filter
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
import { Input } from "@/components/ui/input";

// Mock data for pending settlements
const settlements = [
  {
    id: "SET-001",
    date: "2024-07-10",
    salesRep: "Ahmed Ali",
    vehicle: "V-102 (Toyota Hiace)",
    totalSales: 1250.00,
    cashCollected: 900.00,
    creditSales: 350.00,
    variance: -15.50,
    status: "pending"
  },
  {
    id: "SET-002",
    date: "2024-07-10",
    salesRep: "Sara Smith",
    vehicle: "V-105 (Isuzu Elf)",
    totalSales: 2100.00,
    cashCollected: 1800.00,
    creditSales: 300.00,
    variance: 0.00,
    status: "verified"
  }
];

export default function SettlementPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Van Settlement & Reconciliation</h1>
          <p className="text-slate-500">Verify end-of-day sales and inventory reports from the field.</p>
        </div>
        <Button>
          <Calendar className="mr-2 h-4 w-4" /> Daily Report
        </Button>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Pending Approvals</CardTitle>
            <Truck className="h-4 w-4 text-slate-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">5</div>
            <p className="text-xs text-slate-500">Vans waiting for settlement</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Daily Cash</CardTitle>
            <DollarSign className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">$14,250.00</div>
            <p className="text-xs text-slate-500">Collected from all routes</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Inventory Variance</CardTitle>
            <AlertCircle className="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">-$42.00</div>
            <p className="text-xs text-slate-500">Total discrepancies today</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div className="relative max-w-sm w-full">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
              <Input placeholder="Search rep or vehicle..." className="pl-10" />
            </div>
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm">
                <Filter className="mr-2 h-4 w-4" /> Filter
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date / ID</TableHead>
                <TableHead>Sales Rep / Vehicle</TableHead>
                <TableHead>Total Sales</TableHead>
                <TableHead>Cash / Credit</TableHead>
                <TableHead>Variance</TableHead>
                <TableHead>Status</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {settlements.map((item) => (
                <TableRow key={item.id}>
                  <TableCell>
                    <div className="font-medium text-sm">{item.date}</div>
                    <div className="text-[10px] text-slate-400">{item.id}</div>
                  </TableCell>
                  <TableCell>
                    <div className="font-medium text-sm">{item.salesRep}</div>
                    <div className="text-xs text-slate-500">{item.vehicle}</div>
                  </TableCell>
                  <TableCell className="font-bold">${item.totalSales.toFixed(2)}</TableCell>
                  <TableCell>
                    <div className="text-xs">
                      <div className="text-green-600">C: ${item.cashCollected.toFixed(2)}</div>
                      <div className="text-blue-600">R: ${item.creditSales.toFixed(2)}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className={cn(
                      "text-sm font-medium",
                      item.variance < 0 ? "text-red-600" : item.variance > 0 ? "text-amber-600" : "text-slate-600"
                    )}>
                      {item.variance === 0 ? "None" : `$${item.variance.toFixed(2)}`}
                    </span>
                  </TableCell>
                  <TableCell>
                    <Badge variant={item.status === "verified" ? "default" : "secondary"}>
                      {item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <div className="flex justify-end gap-2">
                      <Button variant="ghost" size="icon" title="View Details">
                        <FileText className="h-4 w-4" />
                      </Button>
                      {item.status === "pending" && (
                        <Button size="sm" className="h-8">
                          Approve
                        </Button>
                      )}
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}

function cn(...classes: any[]) {
  return classes.filter(Boolean).join(" ");
}
