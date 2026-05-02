import type React from 'react';
import type { FieldSchema } from '../components/form-field';
import type { Block, DashboardWidget } from './types';

export type FieldRendererProps = {
    field: FieldSchema;
    value: unknown;
    error?: string;
    onChange: (value: unknown) => void;
};

export type WidgetRendererProps<W = DashboardWidget> = {
    widget: W;
};

export type BlockRendererProps<B = Block> = {
    block: B;
    panelPath: string;
    slug: string;
};

export type ColumnRendererProps<C = unknown> = {
    column: C;
    value: unknown;
    __?: (key: string) => string;
};

export type FilterRendererProps<F = unknown> = {
    filter: F;
    query: Record<string, unknown>;
    pending: Record<string, unknown>;
    stage: (path: string, value: unknown) => void;
    navigate: (mutator: (q: Record<string, unknown>) => Record<string, unknown>) => void;
    __?: (key: string, replacements?: Record<string, string | number>) => string;
};

export type FieldRenderer = React.ComponentType<FieldRendererProps>;
export type WidgetRenderer = React.ComponentType<WidgetRendererProps<any>>;
export type BlockRenderer = React.ComponentType<BlockRendererProps<any>>;
export type ColumnRenderer = React.ComponentType<ColumnRendererProps<any>>;
export type FilterRenderer = React.ComponentType<FilterRendererProps<any>>;
export type PageComponent = React.ComponentType<any>;

type Registry = {
    pages: Record<string, PageComponent>;
    fields: Record<string, FieldRenderer>;
    widgets: Record<string, WidgetRenderer>;
    blocks: Record<string, BlockRenderer>;
    columns: Record<string, ColumnRenderer>;
    filters: Record<string, FilterRenderer>;
};

export const registry: Registry = {
    pages: {},
    fields: {},
    widgets: {},
    blocks: {},
    columns: {},
    filters: {},
};

export type MonorailConfig = Partial<Registry>;

export function applyOverrides(config: MonorailConfig): void {
    if (config.pages) Object.assign(registry.pages, config.pages);
    if (config.fields) Object.assign(registry.fields, config.fields);
    if (config.widgets) Object.assign(registry.widgets, config.widgets);
    if (config.blocks) Object.assign(registry.blocks, config.blocks);
    if (config.columns) Object.assign(registry.columns, config.columns);
    if (config.filters) Object.assign(registry.filters, config.filters);
}
