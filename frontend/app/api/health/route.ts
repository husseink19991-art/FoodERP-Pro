/**
 * Health check endpoint for Docker container monitoring
 * Provides system status, readiness, and liveness probes
 */

import { NextResponse } from 'next/server';

// Simple in-memory state tracker
const healthState = {
  ready: true,
  startTime: new Date(),
  requestCount: 0,
  errorCount: 0,
};

/**
 * Track requests for metrics
 */
export function middleware(request: Request) {
  healthState.requestCount++;
}

/**
 * GET /api/health - Basic health status
 * Used by Docker healthcheck and load balancers
 */
export async function GET(request: Request) {
  const url = new URL(request.url);
  const probe = url.searchParams.get('probe') || 'status';

  try {
    switch (probe) {
      case 'live':
        // Liveness probe: is the app running?
        return NextResponse.json(
          {
            status: 'alive',
            timestamp: new Date().toISOString(),
          },
          { status: 200 }
        );

      case 'ready':
        // Readiness probe: is the app ready to accept traffic?
        if (healthState.ready) {
          return NextResponse.json(
            {
              status: 'ready',
              uptime: new Date().getTime() - healthState.startTime.getTime(),
              timestamp: new Date().toISOString(),
            },
            { status: 200 }
          );
        } else {
          return NextResponse.json(
            {
              status: 'not_ready',
              message: 'Application is initializing',
            },
            { status: 503 }
          );
        }

      case 'metrics':
        // Metrics probe: performance data
        return NextResponse.json(
          {
            status: 'ok',
            metrics: {
              uptime: new Date().getTime() - healthState.startTime.getTime(),
              requestCount: healthState.requestCount,
              errorCount: healthState.errorCount,
              memory: process.memoryUsage(),
            },
            timestamp: new Date().toISOString(),
          },
          { status: 200 }
        );

      case 'deep':
        // Deep health check: verify external dependencies
        try {
          // Check backend connectivity
          const backendUrl = process.env.NEXT_PUBLIC_API_URL || '/api';
          const healthRes = await fetch(`${backendUrl}/health`, {
            method: 'GET',
            signal: AbortSignal.timeout(5000),
          }).catch(() => null);

          const backendHealthy = healthRes?.ok ?? false;

          return NextResponse.json(
            {
              status: backendHealthy ? 'healthy' : 'degraded',
              dependencies: {
                backend: backendHealthy ? 'healthy' : 'unhealthy',
              },
              timestamp: new Date().toISOString(),
            },
            { status: backendHealthy ? 200 : 503 }
          );
        } catch (error) {
          return NextResponse.json(
            {
              status: 'unhealthy',
              error: 'Failed to check dependencies',
            },
            { status: 503 }
          );
        }

      default:
        // Default status
        return NextResponse.json(
          {
            status: 'ok',
            service: 'fooderp-frontend',
            version: process.env.NEXT_PUBLIC_APP_VERSION || '0.1.0',
            uptime: new Date().getTime() - healthState.startTime.getTime(),
            timestamp: new Date().toISOString(),
          },
          { status: 200 }
        );
    }
  } catch (error) {
    healthState.errorCount++;
    return NextResponse.json(
      {
        status: 'error',
        message: error instanceof Error ? error.message : 'Unknown error',
      },
      { status: 500 }
    );
  }
}
