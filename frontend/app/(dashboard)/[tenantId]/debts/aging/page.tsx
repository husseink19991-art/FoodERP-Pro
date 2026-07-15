"use client";

import { 
  BarChart3, 
  AlertCircle, 
  TrendingDown, 
  Calendar,
  Search,
  ArrowRight
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
import { Progress } from "@/components/ui/progress";
import { Badge } from "@/components/ui/badge";

// Mock data for aging
const agingData = [
  {
    id: "1",
    customer: "Al-Baraka Supermarket",
    total: 5200.00,
    current: 3000.00,
    overdue_1_30: 1500.00,
    overdue_31_60: 700.00,
    overdue_60_plus: 0.00,
    risk: "medium"
  },
  {
    id: "2",
    customer: "Grand City Mall",
    total: 8400.00,
    current: 2000.00,
    overdue_1_30: 2400.00,
    overdue_31_60: 3000.00,
    overdue_60_plus: 1000.00,
    risk: "high"
  },
  {
    id: "3",
    customer: "Fresh Food Corner",
    total: 1200.00,
    current: 1200.00,
    overdue_1_30: 0.00,
    overdue_31_60: 0.00,
    overdue_60_plus: 0.00,
    risk: "low"
  }
];

export default function DebtAgingReportPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Debt Aging Report</h1>
          <p className="text-slate-500">Analysis of outstanding customer receivables by time periods.</p>
        </div>
        <Button>Download Full Report</Button>
      </div>

      <div className="grid gap-6 md:grid-cols-4">
        <Card className="bg-slate-50">
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-slate-500">Current (0-30 Days)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">$6,200</div>
            <Progress value={60} className="h-1 mt-2" />
          </CardContent>
        </Card>
        <Card className="bg-amber-50">
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-amber-600">Overdue (31-60 Days)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-amber-600">$3,900</div>
            <Progress value={25} className="h-1 mt-2 bg-amber-200" />
          </CardContent>
        </Card>
        <Card className="bg-orange-50">
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-orange-600">Overdue (61-90 Days)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-orange-600">$3,700</div>
            <Progress value={10} className="h-1 mt-2 bg-orange-200" />
          </CardContent>
        </Card>
        <Card className="bg-red-50">
          <CardHeader className="pb-2">
            <CardTitle className="text-xs font-medium text-red-600">Overdue (90+ Days)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-600">$1,000</div>
            <Progress value={5} className="h-1 mt-2 bg-red-200" />
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Customer Receivables Aging</CardTitle>
          <CardDescription>Breakdown of outstanding balance for each customer.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Customer</TableHead>
                <TableHead className="text-right">Total Balance</TableHead>
                <TableHead className="text-right">Current</TableHead>
                <TableHead className="text-right">1-30 Days</TableHead>
                <TableHead className="text-right">31-60 Days</TableHead>
                <TableHead className="text-right">60+ Days</TableHead>
                <TableHead>Risk Level</TableHead>
                <TableHead className="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {agingData.map((item) => (
                <TableRow key={item.id}>
                  <TableCell className="font-medium">{item.customer}</TableCell>
                  <TableCell className="text-right font-bold">${item.total.toFixed(2)}</TableCell>
                  <TableCell className="text-right">${item.current.toFixed(2)}</TableCell>
                  <TableCell className="text-right">${item.overdue_1_30.toFixed(2)}</TableCell>
                  <TableCell className="text-right">${item.overdue_31_60.toFixed(2)}</TableCell>
                  <TableCell className="text-right">${item.overdue_60_plus.toFixed(2)}</TableCell>
                  <TableCell>
                    <Badge variant={
                      item.risk === "high" ? "destructive" : 
                      item.risk === "medium" ? "warning" : "default"
                    }>
                      {item.risk.toUpperCase()}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-right">
                    <Button variant="ghost" size="sm">
                      Statement <ArrowRight className="ml-2 h-3 w-3" />
                    </Button>
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
