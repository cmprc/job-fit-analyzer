import React from 'react';
import { render, screen } from '@testing-library/react';
import Loader from '../components/Loader';

describe('Loader Component', () => {
  test('renders loader with default text', () => {
    render(<Loader />);
    expect(screen.getByText('Loading...')).toBeInTheDocument();
  });

  test('renders loader with custom text', () => {
    render(<Loader text="Custom loading text" />);
    expect(screen.getByText('Custom loading text')).toBeInTheDocument();
  });

  test('renders loader without text', () => {
    render(<Loader text="" />);
    expect(screen.queryByText('Loading...')).not.toBeInTheDocument();
  });

  test('applies custom className', () => {
    const { container } = render(<Loader className="custom-class" />);
    expect(container.firstChild).toHaveClass('custom-class');
  });
});
