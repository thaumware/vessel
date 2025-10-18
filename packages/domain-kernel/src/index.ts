/**
 * Domain Kernel - Shared DDD Building Blocks
 */

export abstract class Entity<T> {
  protected readonly _id: T;
  
  constructor(id: T) {
    this._id = id;
  }
  
  get id(): T {
    return this._id;
  }
  
  equals(entity?: Entity<T>): boolean {
    if (!entity) return false;
    if (this === entity) return true;
    return this._id === entity._id;
  }
}

export abstract class ValueObject<T> {
  protected readonly props: T;
  
  constructor(props: T) {
    this.props = Object.freeze(props);
  }
  
  equals(vo?: ValueObject<T>): boolean {
    if (!vo) return false;
    return JSON.stringify(this.props) === JSON.stringify(vo.props);
  }
}

export type Result<T, E = Error> =
  | { success: true; value: T }
  | { success: false; error: E };

export const Ok = <T>(value: T): Result<T> => ({
  success: true,
  value,
});

export const Err = <E = Error>(error: E): Result<never, E> => ({
  success: false,
  error,
});

export interface DomainEvent {
  occurredOn: Date;
  aggregateId: string;
  eventType: string;
}

export abstract class AggregateRoot<T> extends Entity<T> {
  private _domainEvents: DomainEvent[] = [];
  
  get domainEvents(): DomainEvent[] {
    return this._domainEvents;
  }
  
  protected addDomainEvent(event: DomainEvent): void {
    this._domainEvents.push(event);
  }
  
  clearEvents(): void {
    this._domainEvents = [];
  }
}
