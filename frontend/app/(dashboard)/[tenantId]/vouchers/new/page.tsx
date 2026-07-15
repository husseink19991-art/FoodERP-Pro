"use client";

import { useState } from "react";
import { 
  Banknote, 
  CreditCard, 
  Building2, 
  Calendar, 
  FileText,
  Search,
  Plus,
  ArrowRight
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";

export default function NewVoucherPage() {
  const [voucherType, setVoucherType] = useState<"receipt" | "payment">("receipt");

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Create Voucher</h1>
          <p className="text-slate-500">Log a new receipt or payment transaction.</p>
        </div>
        <div className="flex bg-slate-100 p-1 rounded-lg">
          <Button 
            variant={voucherType === "receipt" ? "default" : "ghost"}
            onClick={() => setVoucherType("receipt")}
            className="rounded-md"
          >
            Receipt (RV)
          </Button>
          <Button 
            variant={voucherType === "payment" ? "default" : "ghost"}
            onClick={() => setVoucherType("payment")}
            className="rounded-md"
          >
            Payment (PV)
          </Button>
        </div>
      </div>

      <div className="grid gap-6 md:grid-cols-3">
        <div className="md:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Voucher Details</CardTitle>
              <CardDescription>Enter basic information about the transaction.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="entity">
                    {voucherType === "receipt" ? "Customer" : "Supplier"}
                  </Label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder={`Select ${voucherType === "receipt" ? "Customer" : "Supplier"}`} />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="1">Al-Baraka Supermarket</SelectItem>
                      <SelectItem value="2">Grand City Mall</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="date">Voucher Date</Label>
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input id="date" type="date" className="pl-10" />
                  </div>
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="amount">Amount</Label>
                <div className="relative">
                  <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                  <Input id="amount" type="number" placeholder="0.00" className="pl-10 text-lg font-bold" />
                </div>
              </div>

              <div className="space-y-3">
                <Label>Payment Method</Label>
                <RadioGroup defaultValue="cash" className="grid grid-cols-3 gap-4">
                  <div>
                    <RadioGroupItem value="cash" id="cash" className="peer sr-only" />
                    <Label
                      htmlFor="cash"
                      className="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                    >
                      <Banknote className="mb-3 h-6 w-6" />
                      Cash
                    </Label>
                  </div>
                  <div>
                    <RadioGroupItem value="check" id="check" className="peer sr-only" />
                    <Label
                      htmlFor="check"
                      className="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                    >
                      <FileText className="mb-3 h-6 w-6" />
                      Check
                    </Label>
                  </div>
                  <div>
                    <RadioGroupItem value="transfer" id="transfer" className="peer sr-only" />
                    <Label
                      htmlFor="transfer"
                      className="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary"
                    >
                      <Building2 className="mb-3 h-6 w-6" />
                      Transfer
                    </Label>
                  </div>
                </RadioGroup>
              </div>

              <div className="space-y-2">
                <Label htmlFor="notes">Notes / Description</Label>
                <Textarea id="notes" placeholder="Enter any additional details..." />
              </div>
            </CardContent>
            <CardFooter className="flex justify-between border-t p-6">
              <Button variant="outline">Cancel</Button>
              <Button className="w-32">Save Voucher</Button>
            </CardFooter>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Account Overview</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm text-slate-500">Current Balance</span>
                <span className="font-bold text-red-600">$5,200.00</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm text-slate-500">Credit Limit</span>
                <span className="font-medium">$10,000.00</span>
              </div>
              <div className="space-y-1">
                <div className="flex justify-between text-[10px] text-slate-500">
                  <span>Utilization</span>
                  <span>52%</span>
                </div>
                <Progress value={52} className="h-1" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Pending Invoices</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {[1, 2].map(i => (
                <div key={i} className="flex items-center justify-between p-2 rounded border bg-slate-50/50">
                  <div className="text-xs">
                    <p className="font-medium">INV-882{i}0</p>
                    <p className="text-slate-500">Due: 2024-07-20</p>
                  </div>
                  <div className="text-right">
                    <p className="text-xs font-bold">$250.00</p>
                    <Button variant="ghost" size="sm" className="h-6 text-[10px] p-0">Allocate</Button>
                  </div>
                </div>
              ))}
              <Button variant="ghost" className="w-full text-[10px] text-slate-500">Auto-Allocate (FIFO)</Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

function DollarSign(props: any) {
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
      <line x1="12" x2="12" y1="2" y2="22" />
      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
    </svg>
  )
}
