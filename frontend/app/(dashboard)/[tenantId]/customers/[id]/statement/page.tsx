"use client";

import { useState } from "react";
import { 
  Download, 
  Printer, 
  Calendar, 
  ArrowUpRight, 
  ArrowDownRight,
  Filter,
  FileText,
  Search
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
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

// Mock data for ledger entries
const ledgerEntries = [
  {
    id: "1",
    date: "2024-07-01",
    type: "invoice",
    reference: "INV-88210",
    description: "Sales Invoice - Van V-102",
    debit: 450.00,
    credit: 0.00,
    balance: 450.00
  },
  {
    id: "2",
    date: "2024-07-05",
    type: "receipt",
    reference: "RV-99102",
    description: "Cash Collection - Receipt Voucher",
    debit: 0.00,
    credit: 200.00,
    balance: 250.00
  },
  {
    id: "3",
    date: "2024-07-08",
    type: "invoice",
    reference: "INV-88345",
    description: "Sales Invoice - Van V-102",
    debit: 320.00,
    credit: 0.00,
    balance: 570.00
  }
];

export default function StatementOfAccountPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Statement of Account</h1>
          <p className="text-slate-500">كشف حساب: Al-Baraka Supermarket (CUST-001)</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline">
            <Printer className="mr-2 h-4 w-4" /> Print
          </Button>
          <Button>
            <Download className="mr-2 h-4 w-4" /> Export PDF
          </Button>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-slate-500">Opening Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-xl font-bold">$0.00</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-slate-500">Total Debit (+)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-xl font-bold text-red-600">$770.00</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-slate-500">Total Credit (-)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-xl font-bold text-green-600">$200.00</div>
          </CardContent>
        </Card>
        <Card className="bg-primary/5 border-primary/20">
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-primary">Current Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-xl font-bold text-primary">$570.00</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div className="flex items-center gap-2">
              <div className="relative w-64">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                <Input placeholder="Select Date Range" className="pl-10" />
              </div>
              <Button variant="outline" size="icon">
                <Filter className="h-4 w-4" />
              </Button>
            </div>
            <div className="text-sm font-medium text-slate-500">
              Showing 3 transactions
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow className="bg-slate-50">
                <TableHead>Date</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Reference</TableHead>
                <TableHead>Description</TableHead>
                <TableHead className="text-right">Debit (+)</TableHead>
                <TableHead className="text-right">Credit (-)</TableHead>
                <TableHead className="text-right">Balance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {ledgerEntries.map((entry) => (
                <TableRow key={entry.id}>
                  <TableCell className="text-sm">{entry.date}</TableCell>
                  <TableCell>
                    <Badge variant={entry.type === "invoice" ? "outline" : "secondary"}>
                      {entry.type.toUpperCase()}
                    </Badge>
                  </TableCell>
                  <TableCell className="font-medium text-sm">{entry.reference}</TableCell>
                  <TableCell className="text-sm text-slate-600">{entry.description}</TableCell>
                  <TableCell className="text-right text-red-600 font-medium">
                    {entry.debit > 0 ? `$${entry.debit.toFixed(2)}` : "-"}
                  </TableCell>
                  <TableCell className="text-right text-green-600 font-medium">
                    {entry.credit > 0 ? `$${entry.credit.toFixed(2)}` : "-"}
                  </TableCell>
                  <TableCell className="text-right font-bold">
                    ${entry.balance.toFixed(2)}
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
