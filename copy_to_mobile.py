#!/usr/bin/env python3
"""
Script to copy entire interface directory to interface/mobile
Creates a complete mirror for mobile use
"""

import os
import shutil
from pathlib import Path

source_dir = Path(__file__).parent / 'interface'
target_dir = Path(__file__).parent / 'interface' / 'mobile'

# Exclude directories to avoid recursion
exclude_dirs = {'mobile', '.git', 'node_modules'}

def should_exclude(path_str, exclude_set):
    """Check if path should be excluded"""
    parts = Path(path_str).parts
    return any(part in exclude_set for part in parts)

def copy_directory(src, dst, exclude_set):
    """Recursively copy directory structure"""
    count = 0
    for root, dirs, files in os.walk(src):
        # Filter out excluded directories
        dirs[:] = [d for d in dirs if d not in exclude_set]
        
        # Calculate relative path
        rel_path = os.path.relpath(root, src)
        if rel_path == '.':
            rel_path = ''
        
        # Create target directory
        target_root = dst / rel_path if rel_path else dst
        target_root.mkdir(parents=True, exist_ok=True)
        
        # Copy files
        for file in files:
            src_file = Path(root) / file
            dst_file = target_root / file
            
            # Skip if already in mobile directory
            if 'mobile' in src_file.parts:
                continue
                
            shutil.copy2(src_file, dst_file)
            count += 1
            if count % 100 == 0:
                print(f"Copied {count} files...")
    
    return count

if __name__ == '__main__':
    print(f"Starting copy process...")
    print(f"Source: {source_dir}")
    print(f"Target: {target_dir}\n")
    
    if not source_dir.exists():
        print(f"Error: Source directory {source_dir} does not exist!")
        exit(1)
    
    # Create target directory
    target_dir.mkdir(parents=True, exist_ok=True)
    
    # Copy files
    total = copy_directory(source_dir, target_dir, exclude_dirs)
    
    print(f"\nCopy completed! Total files copied: {total}")
    print(f"Mobile interface is now available at: {target_dir}")
